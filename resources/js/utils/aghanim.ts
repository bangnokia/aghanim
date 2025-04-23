import { Inertia } from '@inertiajs/inertia';

interface CallOptions {
  onSuccess?: (result: any) => void;
  onError?: (error: any) => void;
  preserveState?: boolean;
}

export const aghanimCall = (action: string, params: any[] = [], options: CallOptions = {}) => {
  Inertia.post(
    '/aghanim/action',
    { action, params },
    {
      preserveState: options.preserveState ?? true,
      onSuccess: (page) => {
        const result = (page.props as any).aghanim?.actionResult;
        options.onSuccess?.(result);
      },
      onError: (errors) => {
        options.onError?.(errors);
      },
    }
  );
};