import { Inertia } from '@inertiajs/inertia';

interface CallOptions {
  onSuccess?: (result: any) => void;
  onError?: (error: any) => void;
  preserveState?: boolean;
  onStart?: () => void;
  onFinish?: () => void;
}

interface AghanimError {
  error: string;
  errors?: Record<string, string[]>;
  status?: number;
}

/**
 * Call a Laravel Action through Aghanim.
 *
 * @param action The action name to call
 * @param params The parameters to pass to the action
 * @param options Options for the call
 */
export const aghanimCall = (action: string, params: any[] = [], options: CallOptions = {}) => {
  // Call onStart callback if provided
  options.onStart?.();

  Inertia.post(
    '/aghanim/action',
    { action, params },
    {
      preserveState: options.preserveState ?? true,
      onSuccess: (page) => {
        const result = (page.props as any).aghanim?.actionResult;
        options.onSuccess?.(result);
        options.onFinish?.();
      },
      onError: (errors) => {
        // Format the error to be more user-friendly
        const formattedError: AghanimError = {
          error: errors.error || 'An error occurred',
          errors: errors.errors,
          status: errors.status
        };

        options.onError?.(formattedError);
        options.onFinish?.();
      },
    }
  );
};

/**
 * Type guard to check if an error is an AghanimError
 */
export const isAghanimError = (error: any): error is AghanimError => {
  return error && typeof error === 'object' && 'error' in error;
};