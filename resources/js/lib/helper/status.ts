export type JobStatus =
  | 'pending'
  | 'in_progress'
  | 'waiting_parts'
  | 'completed'
  | 'delivered'
  | 'cancelled'
  | 'on_hold'; 

export function getJobStatusClasses(status: string): string {
  switch (status) {
    case 'pending':
      return 'bg-blue-100 text-blue-800';

    case 'in_progress':
      return 'bg-yellow-100 text-yellow-800';

    case 'waiting_parts':
      return 'bg-orange-100 text-orange-800';

    case 'completed':
      return 'bg-green-100 text-green-800';

    case 'delivered':
      return 'bg-purple-100 text-purple-800';

    case 'cancelled':
      return 'bg-red-100 text-red-800';

    default:
      return 'bg-gray-100 text-gray-800';
  }
}
