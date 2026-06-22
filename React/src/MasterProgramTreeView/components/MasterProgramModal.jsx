import React, { useState, useEffect } from "react";
import apiService from "../services/apiService";
import Swal from "sweetalert2";

const MasterProgramModal = ({ modalData, onClose, onComplete }) => {
  const [formData, setFormData] = useState({});
  const [errors, setErrors] = useState({});

  useEffect(() => {
    const entityData = modalData.entity || {};

    const formData = {
      domain_code: entityData.domain_code || "", // Used for domain only
      goal_code: entityData.goal_code || "", // Used for goal only
      name: entityData.name || "", // Common name field
      description: entityData.description || "",
    };

    setFormData(formData);
    setErrors({});
  }, [modalData]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    setErrors({ ...errors, [e.target.name]: "" });
  };

  const handleSubmit = async () => {
    try {
      let response;
      const requestData = {
        id: modalData.entity?.id,
        domain_id: modalData.entity?.domain_id || "", // Attach domain_id if adding a goal
        domain_code: formData.domain_code || "", // For domains
        goal_id: modalData.entity?.goal_id || "", // Attach domain_id if adding a goal
        goal_code: formData.goal_code || "", // For goals
        name: formData.name,
        description: formData.description || "",
      };

      switch (modalData.type) {
        case "addDomain":
          response = await apiService.createDomain(requestData);
          break;
        case "editDomain":
          response = await apiService.updateDomain(requestData);
          break;
        case "deleteDomain":
          response = await apiService.deleteDomain(requestData.id);
          break;
        case "addGoal":
          response = await apiService.createGoal(requestData);
          break;
        case "editGoal":
          response = await apiService.updateGoal(requestData);
          break;
        case "deleteGoal":
          response = await apiService.deleteGoal(requestData.id);
          break;
        case "addTarget":
          response = await apiService.createTarget(requestData);
          break;
        case "editTarget":
          response = await apiService.updateTarget(requestData);
          break;
        case "deleteTarget":
          response = await apiService.deleteTarget(requestData.id);
          break;
        default:
          return;
      }

      if (response.data.status === "success") {
        const dataToSend = modalData.type.includes("delete")
          ? modalData.entity
          : response.data.data;
        const actionType = modalData.type.includes("delete")
          ? "delete"
          : modalData.type.includes("add")
          ? "add"
          : "update";

        // Check if dataToSend is not null or empty
        if (dataToSend && Object.keys(dataToSend).length > 0) {
          onComplete(dataToSend, actionType);
          onClose();
          Swal.fire({
            position: "top-end", // Position the toast in the top right corner
            icon: "success", // Success icon
            title: response.data.message, // Display the success message from the response
            showConfirmButton: false, // Hide the confirm button
            timer: 3000, // Duration before the toast disappears (in milliseconds)
            toast: true, // Enable the toast style
          });
        } else {
          Swal.fire({
            title: "Contact Programmer",
            text: "There is a technical issue",
            icon: "warning",
            confirmButtonText: "OK",
            customClass: {
              confirmButton: "btn btn-primary", // Customize with your preferred classes
            },
            buttonsStyling: false, // Required to apply Bootstrap styling
          });
        }
      } else if (
        response.data.status === "error" &&
        response.data.statusText === "Validation_Error"
      ) {
        setErrors(response.data.validationErrors);
      } else {
        Swal.fire({
          title: response.data.statusText,
          text: response.data.message,
          icon: "error",
          confirmButtonText: "OK",
          customClass: {
            confirmButton: "btn btn-danger", // Custom button class, for example, btn-danger
          },
          buttonsStyling: false, // Applies the custom Bootstrap styling
        });
      }
    } catch (error) {
      const errorMessage = error.response?.statusText || error.message;
      Swal.fire({
        title: "Error",
        text: `An unexpected error occurred: ${errorMessage}`,
        icon: "error",
        confirmButtonText: "OK",
        customClass: {
          confirmButton: "btn btn-warning", // Different custom style, if needed
        },
        buttonsStyling: false,
      });
    }
  };

  const getTitle = () => {
    const action = modalData.type.startsWith("add")
      ? "Add"
      : modalData.type.startsWith("edit")
      ? "Edit"
      : "Delete";

    const entity = modalData.type.includes("Domain")
      ? "Domain"
      : modalData.type.includes("Goal")
      ? "Goal"
      : "Target";

    return `${action} ${entity}`;
  };

  return (
    <div
      className="modal show fade"
      tabIndex="-1"
      aria-modal="true"
      style={{
        display: "block",
        transform: "translateY(0)",
        backgroundColor: "rgba(0, 0, 0, 0.5)",
        zIndex: 1050, // Ensure it's above the modal dialog
      }}
    >
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content">
          <div className="modal-header  bg-light p-3">
            <h5 className="modal-title">{getTitle()}</h5>
            <button
              type="button"
              className="btn-close"
              aria-label="Close"
              onClick={onClose}
            ></button>
          </div>
          <div className="modal-body">
            {modalData.type.includes("delete") ? (
              <>
                <p>
                  Are you sure you want to delete this{" "}
                  {getTitle().split(" ")[1]}?
                </p>
                <p>
                  <strong>Code:</strong>{" "}
                  {formData.domain_code ||
                    formData.goal_code ||
                    formData.target_code}
                </p>
                <p>
                  <strong>Name:</strong> {formData.name}
                </p>
              </>
            ) : (
              <>
                {["addDomain", "editDomain"].includes(modalData.type) && (
                  <>
                    <div className="mb-3">
                      <label htmlFor="domain_code" className="form-label">
                        Domain Code
                      </label>
                      <input
                        type="text"
                        className={`form-control ${
                          errors.domain_code ? "is-invalid" : ""
                        }`}
                        id="domain_code"
                        name="domain_code"
                        placeholder="Domain Code"
                        value={formData.domain_code || ""}
                        onChange={handleChange}
                      />
                      {errors.domain_code && (
                        <div className="invalid-feedback">
                          {errors.domain_code}
                        </div>
                      )}
                    </div>
                    <div className="mb-3">
                      <label htmlFor="name" className="form-label">
                        Domain Name
                      </label>
                      <input
                        type="text"
                        className={`form-control ${
                          errors.name ? "is-invalid" : ""
                        }`}
                        id="name"
                        name="name"
                        placeholder="Domain Name"
                        value={formData.name || ""}
                        onChange={handleChange}
                      />
                      {errors.name && (
                        <div className="invalid-feedback">{errors.name}</div>
                      )}
                    </div>
                  </>
                )}
                {["addGoal", "editGoal"].includes(modalData.type) && (
                  <>
                    <div className="mb-3">
                      <label htmlFor="goal_code" className="form-label">
                        Goal Code
                      </label>
                      <input
                        type="text"
                        className={`form-control ${
                          errors.goal_code ? "is-invalid" : ""
                        }`}
                        id="goal_code"
                        name="goal_code"
                        placeholder="Goal Code"
                        value={formData.goal_code || ""}
                        onChange={handleChange}
                      />
                      {errors.goal_code && (
                        <div className="invalid-feedback">
                          {errors.goal_code}
                        </div>
                      )}
                    </div>
                    <div className="mb-3">
                      <label htmlFor="name" className="form-label">
                        Goal Name
                      </label>
                      <input
                        type="text"
                        className={`form-control ${
                          errors.name ? "is-invalid" : ""
                        }`}
                        id="name"
                        name="name"
                        placeholder="Goal Name"
                        value={formData.name || ""}
                        onChange={handleChange}
                      />
                      {errors.name && (
                        <div className="invalid-feedback">{errors.name}</div>
                      )}
                    </div>
                  </>
                )}
                {["addTarget", "editTarget"].includes(modalData.type) && (
                  <div className="mb-3">
                    <label htmlFor="name" className="form-label">
                      Target Name
                    </label>
                    <input
                      type="text"
                      className={`form-control ${
                        errors.name ? "is-invalid" : ""
                      }`}
                      id="name"
                      name="name"
                      placeholder="Target Name"
                      value={formData.name || ""}
                      onChange={handleChange}
                    />
                    {errors.name && (
                      <div className="invalid-feedback">{errors.name}</div>
                    )}
                  </div>
                )}
              </>
            )}
          </div>
          <div className="modal-footer">
            <button
              type="button"
              className="btn btn-primary"
              onClick={handleSubmit}
            >
              {modalData.type.includes("delete") ? "Delete" : "Submit"}
            </button>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={onClose}
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MasterProgramModal;
