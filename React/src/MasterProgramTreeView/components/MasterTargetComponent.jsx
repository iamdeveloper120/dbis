// React/src/MasterProgramTreeView/components/MasterTargetComponent.jsx
const MasterTargetComponent = ({ target, onEditTarget, onDeleteTarget }) => {
  return (
    <li className="list-group-item d-flex justify-content-between align-items-center">
      {target.name}
      <div>
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

export default MasterTargetComponent;
