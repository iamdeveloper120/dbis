import { useState, useEffect } from "react";
import ClientGoalComponent from "./ClientGoalComponent";
import ClientTargetComponent from "./ClientTargetComponent";
import clientApiService from "../services/clientApiService";
import Swal from "sweetalert2";

const ClientDomainComponent = ({
  domain,
  clientId,
  expandAll,
  onGoalLinked,
}) => {
  const [isOpen, setIsOpen] = useState(expandAll);
  const [isLinked, setIsLinked] = useState(domain.is_domain_linked);
  const [activeGoalId, setActiveGoalId] = useState(null); // Track active goal for tab functionality
  const [searchQuery, setSearchQuery] = useState(""); // State for search input

  const handleLinkDomain = async () => {
    try {
      await clientApiService.linkDomain({ client_id: clientId, id: domain.id });
      setIsLinked(true);
      Swal.fire({
        icon: "success",
        title: "Domain linked successfully",
        toast: true,
        position: "top-end",
        timer: 3000,
        showConfirmButton: false,
      });
    } catch (error) {
      Swal.fire("Error", error.message || "Failed to link domain", "error");
    }
  };

  const handleGoalLinked = (goalId) => {
    console.log("Domain ID:", domain.id, "Goal ID:", goalId); // Debugging output
    onGoalLinked(domain.id, goalId); // Pass both domain and goal IDs correctly
    setActiveGoalId(goalId);
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
            {domain.domain_code} - {domain.name}
            <span className="badge bg-dark-subtle text-body ms-1">
              {domain.goals.length}
            </span>
          </a>
        </h5>
        <button
          className="btn btn-sm btn-primary"
          onClick={handleLinkDomain}
          disabled={isLinked}
        >
          {isLinked ? "Linked" : "Add to Client"}
        </button>
      </div>
      <div className={`collapse ${isOpen ? "show" : ""}`}>
        <div className="card-body">
          <div className="row">
            <div className="col-lg-4">
              <h6 className="text-center mb-3">Goals</h6>
              <div
                className="nav nav-pills flex-column nav-pills-tab custom-verti-nav-pills text-center"
                role="tablist"
                aria-orientation="vertical"
              >
                {domain.goals &&
                  domain.goals.map((goal) => (
                    <ClientGoalComponent
                      key={goal.id}
                      goal={goal}
                      clientId={clientId}
                      domainLinked={isLinked}
                      isActive={activeGoalId === goal.id}
                      onClick={() => setActiveGoalId(goal.id)}
                      onGoalLinked={handleGoalLinked} // Pass correctly here
                    />
                  ))}
              </div>
            </div>
            <div className="col-lg-8">
              <div className="tab-content">
                {domain.goals &&
                  domain.goals.map((goal) => (
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
                        <div className="col-8 text-end">
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
                                clientId={clientId}
                                goalLinked={goal.is_goal_linked}
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
