const useGlobal = () => {
  const version = import.meta.env.VITE_APP_VERSION ?? '0.0.0';

  return { appVersion: `v ${version}` };
};

export default useGlobal;
