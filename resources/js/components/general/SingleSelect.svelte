<script lang="ts">
	import * as Select from '@/components/ui/select';

	type Item = {
		value: string;
		label: string;
		disabled?: boolean;
	};

	interface Props {
		items: Item[];
		label?: string;
		placeholder?: string;
		name?: string;
		value?: string;
		width?: string;
	}

	let {
		items,
		label = "",
		placeholder = "Select an option",
		name,
		width = "w-[180px]",
		value = $bindable("")
	}: Props = $props();

	const triggerContent = $derived(
		items.find((i) => i.value === value)?.label ?? placeholder
	);
</script>

<Select.Root type="single" bind:value name={name}>
	<Select.Trigger class={width}>
		{triggerContent}
	</Select.Trigger>

	<Select.Content>
		<Select.Group>
			{#if label}
				<Select.Label>{label}</Select.Label>
			{/if}

			{#each items as item (item.value)}
				<Select.Item
					value={item.value}
					label={item.label}
					disabled={item.disabled}
				>
					{item.label}
				</Select.Item>
			{/each}
		</Select.Group>
	</Select.Content>
</Select.Root>
