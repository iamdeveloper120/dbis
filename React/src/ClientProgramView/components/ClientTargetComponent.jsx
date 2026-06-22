// React/src/ClientProgramTreeView/components/ClientTargetComponent.jsx
const ClientTargetComponent = ({
  target,
  goalId,
  clientId,
  probeSetType,
  onEditTarget,
  onDeleteTarget,
  onToggleOnHold,
  onManageChaining,
  onManageSteps,
}) => {
  return (
    <li className="list-group-item d-flex justify-content-between align-items-center">
      <div className="d-flex align-items-center gap-2">
        <span>{target.name}</span>
        {Number(target.target_on_hold ?? 0) === 1 && (
          <span className="badge bg-warning-subtle text-warning">On Hold</span>
        )}
      </div>
      <div>
        {probeSetType === "stimulus_program" && (
          <>
            <div className="me-2 d-inline-flex align-items-center gap-2">
              {/* Chaining Method Badge */}
              <span
                className={`badge badge-label ${target.chaining?.method
                  ? "bg-info"
                  : "bg-warning"
                  }`}
              >
                <i class="mdi mdi-circle-medium"></i> {target.chaining?.method
                  ? ` Chain: ${target.chaining.method}`
                  : ` No chain`}
              </span>

              {/* Steps Badge */}
              <span
                className={`badge badge-label ${typeof target.chaining?.total_steps === "number" &&
                  target.chaining.total_steps > 0
                  ? "bg-info"
                  : "bg-warning"
                  }`}
              >
                <i class="mdi mdi-circle-medium"></i> {target.chaining?.total_steps ?? 0} Steps
              </span>
            </div>

            <button
              className="btn btn-sm btn-outline-info btn-icon waves-effect waves-light me-1"
              onClick={(e) => {
                e.stopPropagation();
                onManageChaining(target.id, goalId, clientId);
              }}
            >
              <i className="ri-shuffle-line"></i> {/* Manage Chain */}
            </button>
            <button
              className="btn btn-sm btn-outline-primary btn-icon waves-effect waves-light me-1"
              onClick={(e) => {
                e.stopPropagation();
                onManageSteps(target.id, goalId, clientId);
              }}
            >
              <i className="ri-list-check-2"></i> {/* Manage Steps */}
            </button>
          </>
        )}
        <button
          className={`btn btn-sm ${Number(target.target_on_hold ?? 0) === 1 ? "btn-warning" : "btn-outline-warning"
            } waves-effect waves-light me-1`}
          onClick={(e) => {
            e.stopPropagation();
            onToggleOnHold?.();
          }}
        >
          {Number(target.target_on_hold ?? 0) === 1 ? "Unhold" : "Hold"}
        </button>
        <button
          className="btn btn-sm btn-outline-warning btn-icon waves-effect waves-light me-1"
          onClick={(e) => {
            e.stopPropagation();
            onEditTarget(target);
          }}
        >
          <i className="ri-edit-line"></i>
        </button>
        <button
          className="btn btn-sm btn-outline-danger btn-icon waves-effect waves-light me-1"
          onClick={(e) => {
            e.stopPropagation();
            onDeleteTarget(target);
          }}
        >
          <i className="ri-delete-bin-5-line"></i>
        </button>
      </div>
    </li>
  );
};

export default ClientTargetComponent;
