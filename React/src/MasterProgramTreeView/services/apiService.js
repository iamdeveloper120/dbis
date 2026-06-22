import axios from 'axios';
import Swal from "sweetalert2";

// Retrieve CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Create an Axios instance with default headers
const apiService = axios.create({
  headers: {
    'X-CSRF-TOKEN': csrfToken,
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Helper function to convert data to URLSearchParams
const formatData = (data) => {
  const formData = new URLSearchParams();
  Object.entries(data).forEach(([key, value]) => {
    if (value !== undefined && value !== '') {
      formData.append(key, value);
    }
  });
  return formData;
};

// Add a response interceptor for handling specific errors
apiService.interceptors.response.use(
  (response) => {
    // Allow the successful response to pass through
    return response;
  },
  (error) => {
    if (error.response) {
      if (error.response.status == 401) {
        // Handle "not logged in"
        error.response.statusText =
          "Your session has expired. Please log in again.";
        Swal.fire({
          title: "Session Expired",
          text: "Your session has expired. Please log in again.",
          icon: "warning",
          confirmButtonText: "OK",
          customClass: {
            confirmButton: "btn btn-warning",
          },
          buttonsStyling: false,
        }).then(() => {
          // Redirect to the login page
          window.location.href = "/login"; // Update this to your login page URL
        });
      } else if (error.response.status == 403) {
        // Handle "no permission"
        error.response.statusText = error.response.data.message;
      } else if (error.response.status >= 500) {
        // Handle server errors
        error.response.statusText =
          "There was an issue on the server. Please try again later.";
      }
    } else if (error.request) {
      // Handle network errors
      error.message =
        "Unable to communicate with the server. Please check your network connection.";
    } else {
      // Handle other unexpected errors
      error.message = `An unexpected error occurred: ${error.message}`;
    }

    // Always reject the error to let `catch` blocks handle further details
    return Promise.reject(error);
  }
);

export default {
  fetchTreeData: async () => {
    try {
      const response = await apiService.get('/master-program/tree-view/list');
      console.log(response.data.masterProgram);
      return response.data.masterProgram;
    } catch (error) {
      console.error('Error fetching tree data:', error);
      throw error;
    }
  },

  createDomain: async (data) => apiService.post('/master-program/domains/create', formatData(data)),

  updateDomain: async (data) => apiService.post('/master-program/domains/update', formatData(data)),

  deleteDomain: async (id) => apiService.post('/master-program/domains/delete', formatData({ id })),

  createGoal: async (data) => apiService.post('/master-program/goals/create', formatData(data)),

  updateGoal: async (data) => apiService.post('/master-program/goals/update', formatData(data)),

  deleteGoal: async (id) => apiService.post('/master-program/goals/delete', formatData({ id })),

  createTarget: async (data) => apiService.post('/master-program/targets/create', formatData(data)),

  updateTarget: async (data) => apiService.post('/master-program/targets/update', formatData(data)),

  deleteTarget: async (id) => apiService.post('/master-program/targets/delete', formatData({ id })),
};
