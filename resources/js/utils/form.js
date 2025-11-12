export const extractFormErrors = (error) => {
  const fallback = {
    message:
      error?.userMessage ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to complete the request. Please try again.',
    fieldErrors: {},
  };

  if (!error) {
    return fallback;
  }

  const responseErrors = error?.response?.data?.errors;
  if (responseErrors && typeof responseErrors === 'object') {
    return {
      message:
        error?.response?.data?.message ||
        'Please review the highlighted fields.',
      fieldErrors: responseErrors,
    };
  }

  if (error?.errors && typeof error.errors === 'object') {
    return {
      message: error.message || 'Please review the highlighted fields.',
      fieldErrors: error.errors,
    };
  }

  return fallback;
};

export const resetFormErrors = (state) => {
  if (state) {
    state.message = '';
    state.fieldErrors = {};
  }
};


