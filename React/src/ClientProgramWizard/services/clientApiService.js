// React/src/ClientProgramWizard/services/clientApiService.js
import axios from "axios";
import Swal from "sweetalert2";

const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute("content");

const clientApiService = axios.create({
  headers: {
    "X-CSRF-TOKEN": csrfToken,
    "Content-Type": "application/x-www-form-urlencoded",
    "X-Requested-With": "XMLHttpRequest",
  },
});

const formatData = (data) => {
  const formData = new URLSearchParams();
  Object.entries(data).forEach(([key, value]) => {
    if (value !== undefined && value !== "") {
      formData.append(key, value);
    }
  });
  return formData;
};

// Add a response interceptor for handling specific errors
clientApiService.interceptors.response.use(
  (response) => {
    if (response.data?.status === "error") {
      const error = new Error(response.data.message || "Request failed");
      error.response = response;
      return Promise.reject(error);
    }

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
  fetchClientList: async () => {
    try {
      const response = await clientApiService.get(
        "/client-program/wizard/client-list"
      );
      return response.data.clients;
    } catch (error) {
      Swal.fire("Error", "Failed to load client list", "error");
      throw error;
    }
  },
  fetchClientProgram: async (clientId) => {
    const response = await clientApiService.post(
      "/client-program/wizard/program-list",
      formatData({ client_id: clientId })
    );
    return response.data.masterProgramWizard;
  },
  linkDomain: async (data) =>
    clientApiService.post(
      "/client-program/wizard/domains/create",
      formatData(data)
    ),
  linkGoal: async (data) =>
    clientApiService.post(
      "/client-program/wizard/goals/create",
      formatData(data)
    ),
  linkTarget: async (data) =>
    clientApiService.post(
      "/client-program/wizard/targets/create",
      formatData(data)
    ),
};
