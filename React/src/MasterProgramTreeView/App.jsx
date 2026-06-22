// React/src/MasterProgramTreeView/App.jsx
import { useState, useEffect } from "react";
import apiService from "./services/apiService";
import MasterDomainComponent from "./components/MasterDomainComponent";
import MasterProgramModal from "./components/MasterProgramModal";

const App = () => {
  const [masterProgram, setMasterProgram] = useState([]);
  const [modalData, setModalData] = useState({
    isOpen: false,
    type: "",
    entity: null,
  });
  const [expandAll, setExpandAll] = useState(false);

  useEffect(() => {
    fetchMasterProgramTree();
  }, []);

  const fetchMasterProgramTree = async () => {
    try {
      const response = await apiService.fetchTreeData();
      const formattedData = Object.values(response);
      setMasterProgram(formattedData);
    } catch (error) {
      console.error("Error fetching master program data:", error);
    }
  };

  const toggleExpandAll = () => setExpandAll((prev) => !prev);

  const openModal = (type, entity = null) =>
    setModalData({ isOpen: true, type, entity });
  const closeModal = () =>
    setModalData({ isOpen: false, type: "", entity: null });

  const handleAddEntity = (updatedEntity) => {
    return setMasterProgram((prevProgram) => {
      if (updatedEntity.goal_id) {
        // Add Target under the specified Goal within Domain
        return prevProgram.map((domain) => {
          // Map over each domain, no direct condition on domain.id
          const updatedGoals = domain.goals.map((goal) => {
            if (goal.id === updatedEntity.goal_id) {
              // If the goal matches the goal_id, add the target
              const updatedTargets = [
                { id: updatedEntity.id, name: updatedEntity.name },
                ...(goal.targets || []),
              ];
              return { ...goal, targets: updatedTargets };
            }
            return goal;
          });

          return { ...domain, goals: updatedGoals };
        });
      }  else if (updatedEntity.domain_id && updatedEntity.goal_code) {
        // Adding a new goal within a specific domain
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
              goals: [newGoal,...domain.goals],
            };
          }
          return domain;
        });
      } else {
        // Adding a new domain
        const newDomain = {
          id: updatedEntity.id,
          name: updatedEntity.name,
          domain_code: updatedEntity.domain_code,
          goals: [],
        };
        return [newDomain,...prevProgram];
      }
    });
  };

  const handleUpdateEntity = (updatedEntity) => {
    return setMasterProgram((prevProgram) => {
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
    return setMasterProgram((prevProgram) => {
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

  return (
    <div>
      <div className="card">
        <div className="card-header d-flex justify-content-between align-items-start mb-3">
          <span className="text-start text-wrap" style={{ flex: 1 }}>
            <h4>Master Program</h4>
          </span>

          <div className="d-flex">
            <button
              className="btn btn-sm btn-outline-primary me-1"
              onClick={() => openModal("addDomain")}
            >
              <i className="ri-add-line align-bottom me-1"></i>Add Domain
            </button>
            <button
              className="btn btn-sm btn-outline-secondary"
              onClick={toggleExpandAll}
            >
              {expandAll ? "Collapse All" : "Expand All"}
            </button>
          </div>
        </div>
        <div className="card-body">
          {masterProgram.map((domain) => (
            <MasterDomainComponent
              key={domain.id}
              domain={domain}
              expandAll={expandAll}
              onEditDomain={() => openModal("editDomain", domain)}
              onDeleteDomain={() => openModal("deleteDomain", domain)}
              onAddGoal={() => openModal("addGoal", { domain_id: domain.id })}
              onEditGoal={handleEditGoal} // Pass edit handler
              onDeleteGoal={handleDeleteGoal} // Pass delete handler
              onAddTarget={handleAddEntity} // Pass handleAddTarget here
              onEditTarget={handleEditTarget} // Pass handleEditTarget here
              onDeleteTarget={handleDeleteTarget} // Pass handleDeleteTarget here
            />
          ))}
        </div>
      </div>
      {modalData.isOpen && (
        <MasterProgramModal
          modalData={modalData}
          onClose={closeModal}
          onComplete={handleCrudCompletion}
        />
      )}
    </div>
  );
};

export default App;
