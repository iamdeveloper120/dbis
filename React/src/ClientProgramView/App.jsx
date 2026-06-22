// React/src/ClientProgramTreeView/App.jsx
import { useState, useEffect } from "react";
import Select from "react-select";
import apiService from "./services/apiService";
import ClientDomainComponent from "./components/ClientDomainComponent";
import ClientProgramModal from "./components/ClientProgramModal";
import CustomOption from "./components/CustomOption"; // Import the custom option
import Swal from "sweetalert2";
const App = () => {
  const [clientList, setClientList] = useState([]);
  const [selectedClientId, setSelectedClientId] = useState(null);
  const [selectedClient, setSelectedClient] = useState(null);
  const [clientProgram, setClientProgram] = useState([]);
  const [modalData, setModalData] = useState({
    isOpen: false,
    type: "",
    entity: null,
  });
  const [expandAll, setExpandAll] = useState(false);

  useEffect(() => {
    const fetchClients = async () => {
      try {
        const response = await apiService.fetchClientList();
        const options = response.map((client) => ({
          value: client.id,
          label:
            client.first_name +
            " " +
            client.last_name +
            " (" +
            client.internal_mrn +
            ") ",
        }));
        setClientList(options);
      } catch (error) {
        console.error("Error fetching client list:", error);
        Swal.fire("Error", "Failed to load client list", "error");
      }
    };

    fetchClients();
  }, []);

  const handleClientChange = async (selectedOption) => {
    setClientProgram([]);
    const clientId = selectedOption ? selectedOption.value : null;
    setSelectedClientId(clientId);
    const client = selectedOption || null;
    setSelectedClient(client);

    if (clientId) {
      try {
        const programData = await apiService.fetchClientProgram(clientId);
        console.log(programData);
        setClientProgram(programData);
      } catch (error) {
        console.error("Error fetching client program data:", error);
        Swal.fire("Error", "Failed to load program data", "error");
      }
    }
  };

  const toggleExpandAll = () => setExpandAll((prev) => !prev);

  const openModal = (type, entity = null) =>
    setModalData({ isOpen: true, type, entity });
  const closeModal = () =>
    setModalData({ isOpen: false, type: "", entity: null });

  const handleGoalProbSetUpdate = (goalId, probeSet) => {
    setClientProgram((prevProgram) =>
      prevProgram.map((domain) => {
        // Check if the domain contains the goal we need to update
        const updatedGoals = domain.goals.map((goal) => {
          if (goal.id === goalId) {
            // Update only the probe_set for the matched goal
            return { ...goal, probe_set: probeSet };
          }
          return goal;
        });

        // Return the domain with updated goals if necessary
        return { ...domain, goals: updatedGoals };
      })
    );
  };

  const handleAddEntity = (updatedEntity) => {
    return setClientProgram((prevProgram) => {
      if (updatedEntity.goal_id) {
        // Add Target under the specified Goal within Domain
        return prevProgram.map((domain) => {
          // Map over each domain, no direct condition on domain.id
          const updatedGoals = domain.goals.map((goal) => {
            if (goal.id === updatedEntity.goal_id) {
              // If the goal matches the `goal_id`, add the target
              const updatedTargets = [
                {
                  id: updatedEntity.id,
                  name: updatedEntity.name,
                  target_on_hold: Number(updatedEntity.target_on_hold ?? updatedEntity.on_hold ?? 0),
                },
                ...(goal.targets || []),
              ];
              return { ...goal, targets: updatedTargets };
            }
            return goal;
          });

          return { ...domain, goals: updatedGoals };
        });
      } else if (updatedEntity.domain_id && updatedEntity.goal_code) {
        // Add Goal under the specified Domain
        return prevProgram.map((domain) => {
          if (domain.id === updatedEntity.domain_id) {
            const newGoal = {
              id: updatedEntity.id,
              name: updatedEntity.name,
              goal_code: updatedEntity.goal_code,
              targets: [],
            };
            return {
              ...domain,
              goals: [newGoal, ...domain.goals],
            };
          }
          return domain;
        });
      } else {
        const newDomain = {
          id: updatedEntity.id,
          name: updatedEntity.name,
          domain_code: updatedEntity.domain_code,
          goals: [],
        };
        return [newDomain, ...prevProgram];
      }
    });
  };

  const handleUpdateEntity = (updatedEntity) => {
    return setClientProgram((prevProgram) => {
      if (updatedEntity.goal_id) {
        // Update Target within the specified Goal in Domain
        return prevProgram.map((domain) => {
          // Traverse through each domain without checking for domain.id
          const updatedGoals = domain.goals.map((goal) => {
            if (goal.id === updatedEntity.goal_id) {
              // If goal matches `goal_id`, look for the target to update
              const updatedTargets = goal.targets.map((target) =>
                target.id === updatedEntity.id
                  ? { ...target, ...updatedEntity } // Update the target if ID matches
                  : target
              );
              return { ...goal, targets: updatedTargets };
            }
            return goal;
          });

          return { ...domain, goals: updatedGoals };
        });
      } else if (updatedEntity.domain_id && updatedEntity.goal_code) {
        // Update Goal within the specified Domain
        return prevProgram.map((domain) => {
          if (domain.id === updatedEntity.domain_id) {
            const updatedGoals = domain.goals.map((goal) =>
              goal.id === updatedEntity.id
                ? { ...goal, ...updatedEntity }
                : goal
            );
            return { ...domain, goals: updatedGoals };
          }
          return domain;
        });
      } else {
        // Update Domain at the top level
        return prevProgram.map((item) =>
          item.id === updatedEntity.id ? { ...item, ...updatedEntity } : item
        );
      }
    });
  };

  const handleDeleteEntity = (updatedEntity) => {
    return setClientProgram((prevProgram) => {
      if (updatedEntity.goal_id) {
        // Delete Target within the specified Goal in Domain
        return prevProgram.map((domain) => {
          // Traverse through each domain
          const updatedGoals = domain.goals.map((goal) => {
            if (goal.id === updatedEntity.goal_id) {
              // If goal matches `goal_id`, filter out the target to delete
              const updatedTargets = goal.targets.filter(
                (target) => target.id !== updatedEntity.id
              );
              return { ...goal, targets: updatedTargets };
            }
            return goal;
          });

          return { ...domain, goals: updatedGoals };
        });
      } else if (updatedEntity.domain_id && updatedEntity.goal_code) {
        // Delete Goal within the specified Domain
        return prevProgram.map((domain) => {
          if (domain.id === updatedEntity.domain_id) {
            const updatedGoals = domain.goals.filter(
              (goal) => goal.id !== updatedEntity.id
            );
            return { ...domain, goals: updatedGoals };
          }
          return domain;
        });
      } else {
        // Delete Domain at the top level
        return prevProgram.filter((item) => item.id !== updatedEntity.id);
      }
    });
  };

  const handleCrudCompletion = (updatedEntity, actionType) => {
    console.log(updatedEntity, actionType);
    if (actionType === "add") {
      handleAddEntity(updatedEntity);
    } else if (actionType === "update") {
      handleUpdateEntity(updatedEntity);
    } else if (actionType === "delete") {
      handleDeleteEntity(updatedEntity);
    }

    // Close the modal after updating
    closeModal();
  };

  const handleEditGoal = (goal, domain_id) => {
    openModal("editGoal", { ...goal, domain_id: domain_id });
  };

  const handleDeleteGoal = (goal, domain_id) => {
    openModal("deleteGoal", { ...goal, domain_id: domain_id });
  };

  /*const handleAddTarget = (goal_id) => {
    openModal("addTarget", { goal_id: goal_id });
  };*/

  const handleEditTarget = (target, goal_id) => {
    openModal("editTarget", {
      ...target,
      goal_id: goal_id,
    });
  };

  const handleDeleteTarget = (target, goal_id) => {
    openModal("deleteTarget", {
      ...target,
      goal_id: goal_id,
    });
  };

  const handleStimulusTargetUpdate = (targetId, chainingData) => {
    setClientProgram((prevProgram) =>
      prevProgram.map((domain) => {
        const updatedGoals = domain.goals.map((goal) => {
          const updatedTargets = goal.targets.map((target) =>
            target.id === targetId
              ? {
                ...target,
                chaining: {
                  method: chainingData.method,
                  total_steps: chainingData.total_steps,
                  rule_override: chainingData.rule_override,
                },
              }
              : target
          );

          return { ...goal, targets: updatedTargets };
        });

        return { ...domain, goals: updatedGoals };
      })
    );
  };
  const handleTargetOnHoldUpdate = (targetId, targetOnHold) => {
    setClientProgram((prevProgram) =>
      prevProgram.map((domain) => ({
        ...domain,
        goals: domain.goals.map((goal) => ({
          ...goal,
          targets: goal.targets.map((target) =>
            target.id === targetId
              ? { ...target, target_on_hold: Number(targetOnHold) }
              : target
          ),
        })),
      }))
    );
  };

  const showInfoModal = () => {
    Swal.fire({
      width: "70%",
      icon: "info",
      title: "Guidelines for Client-Specific Program Configuration",
      html: `
        <p>Please note that when you add domains, goals, or targets within the Client Program section, these additions are specific to the selected client only. Any changes or additions made here will apply exclusively to the chosen client’s program and will not affect other client programs, master programs, or any shared content.</p>
        <p>Furthermore, please be aware that the Master Program Wizard operates independently of any individual client. The wizard pulls information solely from the Master Program, ensuring a consistent and standardized framework across all clients. Consequently, any client-specific modifications in the Client Program section will not appear in the Master Program Wizard.</p>
        <p>This structure enables you to tailor programs precisely for each client while preserving the integrity and consistency of the master content across the system.</p>
      `,
      confirmButtonText: "Close",
      customClass: {
        confirmButton: "btn btn-primary", // Customize with your preferred classes

      },
      buttonsStyling: false, // Required to apply Bootstrap styling
    });
  };

  return (
    <div>
      <div className="card ">
        <div className="card-header d-flex justify-content-between align-items-start mb-0">
          <div className="d-flex align-items-center" style={{ flex: 1 }}>
            <Select
              options={clientList}
              onChange={handleClientChange}
              placeholder="Select Client"
              isClearable
              isSearchable
              value={clientList.find(
                (client) => client.value === selectedClientId
              )}
              className="react-select-container"
              classNamePrefix="react-select"
              components={{ Option: CustomOption }} // Use CustomOption here
              styles={{
                container: (base) => ({
                  ...base,
                  flex: 1,
                }),
                control: (base) => ({
                  ...base,
                  backgroundColor: "#fff",
                  color: "#000",
                }),
                singleValue: (base) => ({
                  ...base,
                  color: "#000",
                }),
                menu: (base) => ({
                  ...base,
                  zIndex: 9999,
                }),
              }}
            />
          </div>
        </div>
      </div>
      {selectedClient ? (
        <div className="card">
          <div className="card-header d-flex justify-content-between align-items-start mb-3">
            <span className="text-start text-wrap" style={{ flex: 1 }}>
              <h5>{selectedClient.label} Program</h5>
            </span>

            <div className="d-flex">
              <button
                className="btn btn-sm btn-outline-primary me-1"
                onClick={() => openModal("addDomain")}
              >
                <i className="ri-add-line align-bottom me-1"></i>Add Domain
              </button>
              <button
                className="btn btn-sm btn-outline-secondary me-1"
                onClick={toggleExpandAll}
              >
                {expandAll ? "Collapse All" : "Expand All"}
              </button>
              <button
                className="btn btn-sm btn-outline-secondary btn-icon"
                onClick={showInfoModal}
              >
                <i className="ri-information-line"></i>
              </button>
            </div>
          </div>
          <div className="card-body">
            {clientProgram.map((domain) => (
              <ClientDomainComponent
                key={domain.id}
                domain={domain}
                expandAll={expandAll}
                clientId={selectedClientId} // Pass clientId as a prop here
                onEditDomain={() => openModal("editDomain", domain)}
                onDeleteDomain={() => openModal("deleteDomain", domain)}
                onAddGoal={() => openModal("addGoal", { domain_id: domain.id })}
                onEditGoal={handleEditGoal} // Pass edit handler
                onDeleteGoal={handleDeleteGoal} // Pass delete handler
                onAddTarget={handleAddEntity} // Pass handleAddTarget here
                onEditTarget={handleEditTarget} // Pass handleEditTarget here
                onDeleteTarget={handleDeleteTarget} // Pass handleDeleteTarget here
                onTargetOnHoldUpdate={handleTargetOnHoldUpdate}
                onGoalProbSetUpdate={handleGoalProbSetUpdate}
                onStimulusTargetUpdate={handleStimulusTargetUpdate}
              />
            ))}
          </div>
        </div>
      ) : (
        <div
          className="card"
          style={{
            width: "100%",
            height: "100%", // Set to 100% to fit available space
            overflow: "hidden", // Prevent scrolling
            display: "flex", // Flex layout for centering
            alignItems: "center",
            justifyContent: "center",
          }}
        >
          <div
            className="card-body"
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              flex: 1, // Occupy available height
              padding: "0 10px", // Optional padding for aesthetics
            }}
          >
            <p
              style={{
                fontSize: "1.25rem",
                fontWeight: "200",
                color: "#333",
                backgroundColor: "rgba(255, 255, 255, 0.7)",
                padding: "50px",
                borderRadius: "5px",
              }}
            >
              Please select a client to view the program details.
            </p>
          </div>
        </div>
      )}
      {modalData.isOpen && (
        <ClientProgramModal
          clientId={selectedClientId} // Pass clientId as a prop here
          modalData={modalData}
          onClose={closeModal}
          onComplete={handleCrudCompletion}
        />
      )}
    </div>
  );
};

export default App;
