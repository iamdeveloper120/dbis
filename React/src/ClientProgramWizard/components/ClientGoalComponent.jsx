import { useState } from "react";
import clientApiService from "../services/clientApiService";
import Swal from "sweetalert2";

const ClientGoalComponent = ({
  goal,
  clientId,
  domainLinked,
  isActive,
  onClick,
  onGoalLinked
}) => {
  const [isLinked, setIsLinked] = useState(goal.is_goal_linked);

  const handleLinkGoal = async () => {
    try {
      await clientApiService.linkGoal({ client_id: clientId, id: goal.id });
      
      setIsLinked(true);
      onGoalLinked(goal.id); // Directly pass goal.id when notifying parent
      console.log(goal.id);
      Swal.fire({
        icon: "success",
        title: "Goal linked successfully",
        toast: true,
        position: "top-end",
        timer: 3000,
        showConfirmButton: false,
      });
    } catch (error) {
      Swal.fire("Error", error.message || "Failed to link goal", "error");
    }
  };

  return (
    <button
      className={`nav-link ${isActive ? "active" : ""}`}
      onClick={onClick}
      id={`goal_${goal.id}-tab`}
      data-bs-toggle="pill"
      role="tab"
      aria-controls={`goal_${goal.id}`}
      aria-selected={isActive}
    >
      <div className="d-flex justify-content-between align-items-start">
        {/* Goal text aligned left with wrapping */}
        <span className="text-start text-wrap" style={{ flex: 1 }}>
          {goal.goal_code} - {goal.name} <span className="badge  bg-dark-subtle text-body ms-1">{goal.targets.length}</span>
        </span>
        {/* Buttons aligned to the right */}
        <div className="d-flex">
          <button
            className="btn btn-sm btn-warning waves-effect waves-light me-1 ms-1"
            onClick={(e) => {
              e.stopPropagation();
              handleLinkGoal();
            }}
            disabled={!domainLinked || isLinked}
          >
            {isLinked ? "Linked" : "Add to Client"}
          </button>
        </div>
      </div>
    </button>
  );
};

export default ClientGoalComponent;
