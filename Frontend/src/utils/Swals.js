import Swal from 'sweetalert2';

const configMap = {
    200: { icon: 'success', title: 'Success', confirmButtonColor: '#10B981', autoClose: true },
    201: { icon: 'success', title: 'Created Successfully', confirmButtonColor: '#059669', autoClose: true },
    400: { icon: 'warning', title: 'Bad Request', confirmButtonColor: '#F59E0B', autoClose: false },
    401: { icon: 'warning', title: 'Unauthorized', confirmButtonColor: '#F59E0B', autoClose: false },
    403: { icon: 'warning', title: 'Access Denied', confirmButtonColor: '#EF4444', autoClose: false },
    404: { icon: 'info', title: 'Not Found', confirmButtonColor: '#6B7280', autoClose: false },
    409: { icon: 'warning', title: 'Action Conflict', confirmButtonColor: '#F59E0B', autoClose: false },
    422: { icon: 'warning', title: 'Validation Error', confirmButtonColor: '#EA580C', autoClose: false },
    500: { icon: 'error', title: 'Server Error', confirmButtonColor: '#EF4444', autoClose: false },
    501: { icon: 'info', title: 'Not Implemented', confirmButtonColor: '#6B7280', autoClose: false },
};

const defaultConfig = {
    icon: 'error',
    title: 'Notification',
    confirmButtonColor: '#EF4444',
    autoClose: false
};

export const showStatusAlert = (status, text = '') => {
    const config = configMap[status] || defaultConfig;

    return Swal.fire({
        icon: config.icon,
        title: config.title,
        text,
        confirmButtonColor: config.confirmButtonColor,
        timer: config.autoClose ? 2000 : undefined,
        showConfirmButton: !config.autoClose,
    });
};

export const showConfirm = async (
    title = 'Are you sure?',
    text = 'This action cannot be undone.',
    confirmButtonText = 'Yes, proceed',
    icon = 'warning'
) => {
    const result = await Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#EF4444',
        confirmButtonText,
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

export const showSuccess = (text = 'Saved successfully', title = 'Success') => {
    return showStatusAlert(200, text);
};

export const showError = (text = 'Something went wrong', title = 'Error') => {
    return showStatusAlert(500, text);
};