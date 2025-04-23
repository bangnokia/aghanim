import { useState } from 'react';
import { aghanimCall } from '../utils/aghanim';

export function useAghanim() {
  const [result, setResult] = useState<any>(null);
  const [error, setError] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  const call = async (action: (...args: any[]) => void, params: any[] = []) => {
    setLoading(true);
    setError(null);

    return new Promise((resolve, reject) => {
      aghanimCall(action.name, params, {
        onSuccess: (data) => {
          setResult(data);
          setLoading(false);
          resolve(data);
        },
        onError: (err) => {
          setError(err);
          setLoading(false);
          reject(err);
        },
      });
    });
  };

  return { call, result, error, loading };
}