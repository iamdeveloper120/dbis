const MasterGoalComponent = ({
  goal,
  isActive,
  onClick,
  onEditGoal,
  onDeleteGoal,
}) => {
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
            className="btn btn-sm btn-warning btn-icon waves-effect waves-light me-1 ms-1"
            onClick={(e) => {
              e.stopPropagation();
              onEditGoal(goal);
            }}
          >
            <i className="ri-edit-line"></i>
          </button>
          <button
            className="btn btn-sm btn-danger btn-icon waves-effect waves-light"
            onClick={(e) => {
              e.stopPropagation();
              onDeleteGoal(goal);
            }}
          >
            <i className="ri-delete-bin-5-line"></i>
          </button>
        </div>
      </div>
    </button>
  );
};

export default MasterGoalComponent;
