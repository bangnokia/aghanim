import { useState } from 'react';
import { aghanimCall, isAghanimError } from '../utils/aghanim';

/**
 * Hook for calling Aghanim actions with state management.
 */
export function useAghanim() {
  const [result, setResult] = useState<any>(null);
  const [error, setError] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  /**
   * Call an Aghanim action.
   *
   * @param action The action to call (use the generated aghanim.actions.* functions)
   * @param params The parameters to pass to the action
   * @returns A promise that resolves with the action result
   */
  const call = async (action: (...args: any[]) => void, params: any[] = []) => {
    setLoading(true);
    setError(null);

    return new Promise((resolve, reject) => {
      aghanimCall(action.name, params, {
        onStart: () => {
          setLoading(true);
          setError(null);
        },
        onSuccess: (data) => {
          setResult(data);
          resolve(data);
        },
        onError: (err) => {
          setError(err);
          reject(err);
        },
        onFinish: () => {
          setLoading(false);
        }
      });
    });
  };

  /**
   * Reset the state of the hook.
   */
  const reset = () => {
    setResult(null);
    setError(null);
    setLoading(false);
  };

  /**
   * Check if the current error is a validation error.
   */
  const hasValidationErrors = () => {
    return isAghanimError(error) && error.errors && Object.keys(error.errors).length > 0;
  };

  /**
   * Get validation errors for a specific field.
   *
   * @param field The field name
   * @returns Array of error messages or undefined
   */
  const getValidationErrors = (field: string) => {
    if (hasValidationErrors() && error.errors && error.errors[field]) {
      return error.errors[field];
    }
    return undefined;
  };

  return {
    call,
    result,
    error,
    loading,
    reset,
    hasValidationErrors,
    getValidationErrors
  };
}