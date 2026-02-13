<script>
    import FilePond, { registerPlugin } from 'svelte-filepond';
    import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
    import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
    import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
    import { FileStatus } from 'filepond';
    import _ from 'lodash';

    registerPlugin(
        FilePondPluginImageExifOrientation,
        FilePondPluginImagePreview,
        FilePondPluginFileValidateType
    );

    let pond;
    let name = 'upload-files[]';
    let { csrf_token, disableFormSubmit = $bindable(), files = $bindable() } = $props();
    let labelFileProcessingError = $state('Error during upload');
    let labelFileProcessingDefaultError = 'Error during upload';

    const BLOCKING_STATUSES = new Set([
        FileStatus.PROCESSING,
        FileStatus.PROCESSING_QUEUED,
        FileStatus.PROCESSING_ERROR,
        FileStatus.LOADING,
        FileStatus.LOAD_ERROR,
    ]);

    $effect(() => {
        const hasBlockingFiles = files.some(file => 
            BLOCKING_STATUSES.has(file.status)
        );
        disableFormSubmit = hasBlockingFiles;
    });

    function handleProcessFile() {
      files = [...pond.getFiles()];
    }

    function handleUpdateFiles(event) {}
</script>

<div>
    <FilePond
        bind:this={pond}
        bind:files={files}
        {name}
        acceptedFileTypes={['image/jpeg', 'image/png']}
        allowMultiple={true}
        onupdatefiles={handleUpdateFiles}
        onprocessfile={handleProcessFile}
        onprocessfileerror={handleProcessFile}
        onprocessfileabort={handleProcessFile}
        onaddfile={handleProcessFile}
        onremovefile={handleProcessFile}
        labelFileProcessingError={labelFileProcessingError}
        credits={false}
        server={{
            process: {
                url: '/upload-job-files',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                onerror: res => {
                  const response = JSON.parse(res);
                  labelFileProcessingError = _.get(response, ['upload-files.0', 0], labelFileProcessingDefaultError);
                  return res; 
                },
                onload: (res) => res
            },
            load: '/uploads/images/',
            revert: {
                url: '/upload-job-files',
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': csrf_token
                }
            }
        }}
    />
</div>

<style global>
    @import 'filepond/dist/filepond.css';
    @import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
</style>