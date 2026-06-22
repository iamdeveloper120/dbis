import { useState, useEffect } from "react";
import MasterGoalComponent from "./MasterGoalComponent";
import MasterTargetComponent from "./MasterTargetComponent";
import apiService from "../services/apiService";
import Swal from "sweetalert2";
const MasterDomainComponent = ({
  domain,
  expandAll,
  onEditDomain,
  onDeleteDomain,
  onAddGoal,
  onEditGoal,
  onDeleteGoal,
  onAddTarget,
  onEditTarget,
  onDeleteTarget,
}) => {
  const [isOpen, setIsOpen] = useState(expandAll);
  const [activeGoalId, setActiveGoalId] = useState(null); // Track active goal for tab functionality
  const [targetName, setTargetName] = useState(""); // Local state for the target name
  const [error, setError] = useState(""); // Local state for validation error
  const [searchQuery, setSearchQuery] = useState(""); // State for search input

  const handleTargetNameChange = (e) => {
    setTargetName(e.target.value);
    setError("");
  };
  const handleAddTargetClick = async (goalId) => {
    if (!targetName.trim()) {
      setError("Target name cannot be empty");
      return;
    }
    try {
      // Make an API call to create a new target
      const response = await apiService.createTarget({
        name: targetName,
        goal_id: goalId,
      });

      if (response.data.status === "success") {
        const newTarget = response.data.data;

        // Check if newTarget is valid before updating state
        if (newTarget && Object.keys(newTarget).length > 0) {
          onAddTarget({
            id: newTarget.id,
            name: newTarget.name,
            goal_id: newTarget.goal_id,
          });
          setTargetName("");
          Swal.fire({
            position: "top-end", // Position the toast in the top right corner
            icon: "success", // Success icon
            title: response.data.message, // Display the success message from the response
            showConfirmButton: false, // Hide the confirm button
            timer: 3000, // Duration before the toast disappears (in milliseconds)
            toast: true, // Enable the toast style
          });
        } else {
          Swal.fire(
            "Contact Programer",
            "There is a technical issue",
            "warning"
          );
        }
      } else if (
        response.data.status === "error" &&
        response.data.statusText === "Validation_Error"
      ) {
        setError(response.data.validationErrors.name || "Invalid input");
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
        text: `${errorMessage}`,
        icon: "error",
        confirmButtonText: "OK",
        customClass: {
          confirmButton: "btn btn-warning", // Different custom style, if needed
        },
        buttonsStyling: false,
      });
    }
  };

  useEffect(() => {
    setIsOpen(expandAll);
  }, [expandAll]);
  return (
    <div className="card border card-border-primary shadow-none mb-3">
      <div className="card-header d-flex justify-content-between align-items-center pt-1 pb-1">
        <h5 className="mb-0">
          <a
            className="cursor-pointer"
            onClick={() => setIsOpen(!isOpen)}
            aria-expanded={isOpen}
          >
            {domain.domain_code} - {domain.name} <span className="badge  bg-dark-subtle text-body ms-1">{domain.goals.length}</span>
          </a>
        </h5>
        <div>
          <button
            className="btn btn-sm btn-warning btn-icon waves-effect waves-light me-1"
            onClick={onEditDomain}
          >
            <i className="ri-edit-line"></i>
          </button>
          <button
            className="btn btn-sm btn-danger btn-icon waves-effect waves-light me-1"
            onClick={onDeleteDomain}
          >
            <i className="ri-delete-bin-5-line"></i>
          </button>
          <button
            className="btn btn-sm btn-secondary"
            onClick={() => onAddGoal()}
          >
            <i className="ri-add-line align-bottom me-1"></i>Add Goal
          </button>
        </div>
      </div>
      <div className={`collapse ${isOpen ? "show" : ""}`}>
        <div className="card-body">
          <div className="row">
            <div className="col-lg-4">
              {/* Goals heading */}
              <h6 className="text-center mb-3">Goals</h6>
              <div
                className="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center"
                role="tablist"
                aria-orientation="vertical"
              >
                {domain.goals &&
                  Object.values(domain.goals).map((goal) => (
                    <MasterGoalComponent
                      key={goal.id}
                      goal={goal}
                      isActive={activeGoalId === goal.id}
                      onClick={() => setActiveGoalId(goal.id)} // Activate goal tab
                      onEditGoal={() => onEditGoal(goal, domain.id)} // Pass domain_id here
                      onDeleteGoal={() => onDeleteGoal(goal, domain.id)} // Pass down the goal to delete
                    />
                  ))}
              </div>
            </div>

            {/* Target content for the selected goal */}
            <div className="col-lg-8">
              <div className="tab-content">
                {domain.goals &&
                  Object.values(domain.goals).map((goal) => (
                    <div
                      key={goal.id}
                      className={`tab-pane fade ${
                        activeGoalId === goal.id ? "show active" : ""
                      }`}
                      id={`goal_${goal.id}`}
                    >
                      {/* Row for heading and button */}
                      <div className="row align-items-center m-1 border-bottom border-primary pb-3">
                        {/* Targets Label - 4 columns */}
                        <div className="col-4">
                          <h6 className="mb-0 text-start">Targets</h6>
                        </div>

                        {/* Target Input and Button Section - 8 columns */}
                        <div className="col-8">
                          <div className="d-flex align-items-start">
                            {/* Input Field */}
                            <div className="flex-grow-1 me-2">
                              <input
                                type="text"
                                className="form-control"
                                placeholder="Enter target name"
                                value={targetName}
                                onChange={(e) => handleTargetNameChange(e)}
                              />
                              {/* Error Message */}
                              {error && (
                                <p className="text-danger mt-1 mb-0">{error}</p>
                              )}
                            </div>

                            {/* Add Target Button */}
                            <button
                              className="btn btn-outline-secondary"
                              onClick={() => handleAddTargetClick(goal.id)}
                            >
                              <i className="ri-add-line align-bottom me-1"></i>
                              Add Target
                            </button>
                          </div>
                        </div>
                      </div>
                      <div className="row align-items-center m-0 pb-1">
                        <div className="col-12 p-0">
                          <input
                            type="text"
                            className="form-control"
                            placeholder="Search targets"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                          />
                        </div>
                      </div>

                      {/* List of targets */}
                      <ul className="list-group">
                        {goal.targets &&
                          Object.values(goal.targets)
                            .filter((target) =>
                              target.name
                                .toLowerCase()
                                .includes(searchQuery.toLowerCase())
                            )
                            .map((target) => (
                              <MasterTargetComponent
                                key={target.id}
                                target={target}
                                onEditTarget={() =>
                                  onEditTarget(target, goal.id)
                                }
                                onDeleteTarget={() =>
                                  onDeleteTarget(target, goal.id)
                                }
                              />
                            ))}
                      </ul>
                    </div>
                  ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MasterDomainComponent;
