import { useState, useEffect } from "react";
import clientApiService from "../services/clientApiService";
import Swal from "sweetalert2";

const ClientTargetComponent = ({ target, clientId, goalLinked }) => {
  const [isLinked, setIsLinked] = useState(target.is_target_linked);

  const handleLinkTarget = async () => {
    try {
      await clientApiService.linkTarget({ client_id: clientId, id: target.id });
      setIsLinked(true);
      Swal.fire({
        icon: "success",
        title: "Target linked successfully",
        toast: true,
        position: "top-end",
        timer: 3000,
        showConfirmButton: false,
      });
    } catch (error) {
      Swal.fire("Error", error.message || "Failed to link target", "error");
    }
  };
  useEffect(() => {
    console.log("Goal linked status in ClientTargetComponent:", goalLinked);
  }, [goalLinked]);

  return (
    <li className="list-group-item d-flex justify-content-between align-items-center">
      <span>{target.name}</span>
      <button
        className="btn btn-sm btn-outline-primary"
        onClick={handleLinkTarget}
        disabled={!goalLinked || isLinked}
      >
        {isLinked ? "Linked" : "Add to Client"}
      </button>
    </li>
  );
};

export default ClientTargetComponent;
