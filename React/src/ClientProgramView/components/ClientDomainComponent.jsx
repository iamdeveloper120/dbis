import { useState, useEffect } from "react";
import ClientGoalComponent from "./ClientGoalComponent";
import ClientTargetComponent from "./ClientTargetComponent";
import apiService from "../services/apiService";
import Swal from "sweetalert2";
const ClientDomainComponent = ({
  domain,
  expandAll,
  clientId,
  onEditDomain,
  onDeleteDomain,
  onAddGoal,
  onEditGoal,
  onDeleteGoal,
  onAddTarget,
  onEditTarget,
  onDeleteTarget,
  onTargetOnHoldUpdate,
  onGoalProbSetUpdate,
  onStimulusTargetUpdate,
}) => {
  const [isOpen, setIsOpen] = useState(expandAll);
  const [activeGoalId, setActiveGoalId] = useState(null); // Track active goal for tab functionality
  const [targetName, setTargetName] = useState(""); // Local state for the target name
  const [error, setError] = useState(""); // Local state for validation error
  const [searchQuery, setSearchQuery] = useState(""); // State for search input

  const [offcanvasGoalId, setOffcanvasGoalId] = useState(null);
  const [offcanvasContext, setOffcanvasContext] = useState(null); // 'probe_set', 'steps', 'chaining'
  const [offcanvasTargetId, setOffcanvasTargetId] = useState(null); // Optional — used only for steps/chaining

  // Handler to open "Add New Probe Set" offcanvas
  const handleAddNewProbeSet = (goalId) => {
    if (typeof window.openAddNewProbeSet === "function") {
      console.log("handleAddNewProbeSet: " + goalId);
      setOffcanvasContext("probe_set");
      setOffcanvasGoalId(goalId); // Set the goal for the offcanvas context
      window.openAddNewProbeSet(clientId, goalId);
    }
  };

  // Handler to open "Manage Existing Rules" offcanvas
  const handleManageExistingRules = (goalId) => {
    if (typeof window.openManageExistingRules === "function") {
      console.log("handleManageExistingRules: " + goalId);
      setOffcanvasContext("probe_set");
      setOffcanvasGoalId(goalId); // Set the goal for the offcanvas context
      window.openManageExistingRules(clientId, goalId);
    }
  };

  // Handler to open "View Active Rules" offcanvas
  const handleViewActiveProbeSetRules = (goalId) => {
    if (typeof window.openActiveProbeSetRules === "function") {
      console.log("handleViewActiveProbeSetRules: " + goalId);
      setOffcanvasContext("probe_set");
      setOffcanvasGoalId(goalId); // Set the goal for the offcanvas context
      window.openActiveProbeSetRules(clientId, goalId);
    }
  };

  // Handler for React update when offcanvas is closed
  useEffect(() => {
    window.updateReactComponentAfterClose = () => {
      console.log("Offcanvas closed. Context:", offcanvasContext);
      // Only update goal-level probe set
      if (offcanvasContext === "probe_set" && offcanvasGoalId) {
        // Fetch the updated probe set for the current goal and client
        apiService
          .getClientSelectedGoalProbeSet(clientId, offcanvasGoalId)
          .then((response) => {
            // Update the component's data with the new probe set information
            console.log(response.data);
            onGoalProbSetUpdate(offcanvasGoalId, response.data);
          })
          .catch((error) => console.error("Error fetching probe set:", error))
          .finally(() => {
            // Reset `offcanvasGoalId` after the operation completes
            setOffcanvasContext(null);
            setOffcanvasGoalId(null);
          });
      }
      if (
        (offcanvasContext === "steps" || offcanvasContext === "chaining") &&
        offcanvasTargetId
      ) {
        // Fetch the updated probe set for the current goal and client
        apiService
          .getClientSelectedStimulusTargetUpdatedDetail(
            clientId,
            offcanvasTargetId
          )
          .then((response) => {
            // Update the component's data with the new probe set information
            console.log(response);
            onStimulusTargetUpdate(offcanvasTargetId, response.data.chaining);
          })
          .catch((error) =>
            console.error("Error fetching Stimulus target detail:", error)
          )
          .finally(() => {
            // Reset `offcanvasGoalId` after the operation completes
            setOffcanvasContext(null);
            setOffcanvasTargetId(null);
          });
      }
    };

    return () => {
      // Clean up when component unmounts or dependency changes
      delete window.updateReactComponentAfterClose;
    };
  }, [offcanvasGoalId, offcanvasTargetId]); // Trigger on changes to `offcanvasGoalId`

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
        client_id: clientId,
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
            target_on_hold: Number(newTarget.target_on_hold ?? newTarget.on_hold ?? 0),
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

  /************************************** */
  const handleManageChaining = (targetId, goalId, clientId) => {
    console.log("Chaining requested for:", {
      targetId,
      goalId,
      clientId,
    });

    if (typeof window.openStimulusStepsEditor === "function") {
      setOffcanvasContext("chaining");
      setOffcanvasTargetId(targetId);
      window.openStimulusChainEditor(clientId, goalId, targetId);
    }
  };
  const handleManageSteps = (targetId, goalId, clientId) => {
    console.log("Steps requested for:", {
      targetId,
      goalId,
      clientId,
    });

    if (typeof window.openStimulusStepsEditor === "function") {
      setOffcanvasContext("steps");
      setOffcanvasTargetId(targetId);
      window.openStimulusStepsEditor(clientId, goalId, targetId);
    }
  };
  const handleToggleTargetOnHold = async (target) => {
    const nextOnHold = Number(target.target_on_hold) === 1 ? 0 : 1;
    try {
      const response = await apiService.onHoldTarget(target.id, nextOnHold);

      if (response.data.status === "success") {
        const updated = response.data.data || {};
        const value = Number(updated.target_on_hold ?? updated.on_hold ?? nextOnHold);
        onTargetOnHoldUpdate(target.id, value);
        Swal.fire({ position: "top-end", icon: "success", title: response.data.message, showConfirmButton: false, timer: 3000, toast: true });
        return;
      }

      Swal.fire({
        title: response.data.statusText || "Error",
        text: response.data.message || "Unable to update hold state.",
        icon: "error",
        confirmButtonText: "OK",
        customClass: { confirmButton: "btn btn-danger" },
        buttonsStyling: false,
      });
    } catch (error) {
      const errorMessage = error.response?.statusText || error.message;
      Swal.fire({
        title: "Error",
        text: errorMessage,
        icon: "error",
        confirmButtonText: "OK",
        customClass: { confirmButton: "btn btn-warning" },
        buttonsStyling: false,
      });
    }
  };
  /*************************************** */

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
            {domain.domain_code} - {domain.name}{" "}
            <span className="badge bg-dark-subtle text-body ms-1">
              {domain.goals.length}
            </span>
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
                    <ClientGoalComponent
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
                      className={`tab-pane fade ${activeGoalId === goal.id ? "show active" : ""
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
                      <div className="row">
                        <div className="col-lg-12">
                          {goal.probe_set && goal.probe_set.name ? (
                            <div
                              className={`d-flex align-items-center p-2 ${"border border-primary"}`}
                            >
                              <span
                                dangerouslySetInnerHTML={{
                                  __html:
                                    '<i class="ri-user-smile-line me-3 align-middle fs-16 text-primary"></i>',
                                }}
                              ></span>
                              <span className="flex-grow-1">
                                <b>{goal.probe_set.name}</b>(
                                {goal.probe_set.combination_name})
                              </span>

                              <button
                                type="button"
                                className="btn btn-sm btn-outline-primary btn-icon me-2"
                                onClick={() =>
                                  handleViewActiveProbeSetRules(goal.id)
                                }
                              >
                                <i className="ri-eye-line align-bottom"></i>
                              </button>
                              <button
                                type="button"
                                className="btn btn-sm btn-outline-warning me-2"
                                onClick={() =>
                                  handleManageExistingRules(goal.id)
                                }
                              >
                                <i className="ri-edit-line align-bottom me-1"></i>{" "}
                                Update Rules
                              </button>
                              <button
                                type="button"
                                className="btn btn-sm btn-outline-primary"
                                onClick={() => handleAddNewProbeSet(goal.id)}
                              >
                                <i className="ri-add-line align-bottom me-1"></i>{" "}
                                Add Rules
                              </button>
                            </div>
                          ) : (
                            <div
                              className={`d-flex align-items-center p-2 ${"border border-warning"}`}
                            >
                              <span
                                dangerouslySetInnerHTML={{
                                  __html:
                                    '<i class="ri-error-warning-line me-3 align-middle fs-16 text-warning"></i>',
                                }}
                              ></span>
                              <span className="flex-grow-1">
                                No Probe Set Attached
                              </span>
                              <button
                                type="button"
                                className="btn btn-sm btn-outline-primary"
                                onClick={() => handleAddNewProbeSet(goal.id)}
                              >
                                <i className="ri-add-line align-bottom me-1"></i>{" "}
                                Add Rules
                              </button>
                            </div>
                          )}
                        </div>
                      </div>
                      <div className="row align-items-center m-0 pb-1  pt-1">
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
                              <ClientTargetComponent
                                key={target.id}
                                target={target}
                                goalId={goal.id}
                                clientId={clientId}
                                probeSetType={goal.probe_set?.type}
                                onEditTarget={() =>
                                  onEditTarget(target, goal.id)
                                }
                                onDeleteTarget={() =>
                                  onDeleteTarget(target, goal.id)
                                }
                                onToggleOnHold={() => handleToggleTargetOnHold(target)}
                                onManageChaining={(
                                  targetId,
                                  goalId,
                                  clientId
                                ) =>
                                  handleManageChaining(
                                    targetId,
                                    goalId,
                                    clientId
                                  )
                                }
                                onManageSteps={(targetId, goalId, clientId) =>
                                  handleManageSteps(targetId, goalId, clientId)
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

export default ClientDomainComponent;
