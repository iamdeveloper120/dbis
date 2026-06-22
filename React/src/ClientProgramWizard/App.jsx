// React/src/ClientProgramWizard/App.jsx
import { useState, useEffect } from "react";
import Select from "react-select";
import clientApiService from "./services/clientApiService";
import ClientDomainComponent from "./components/ClientDomainComponent";
import CustomOption from "./components/CustomOption"; // Import the custom option
import Swal from "sweetalert2";

const App = () => {
  const [clientList, setClientList] = useState([]);
  const [selectedClientId, setSelectedClientId] = useState(null);
  const [selectedClient, setSelectedClient] = useState(null);
  const [clientProgram, setClientProgram] = useState([]);
  const [expandAll, setExpandAll] = useState(false);

  useEffect(() => {
    const fetchClients = async () => {
      try {
        const response = await clientApiService.fetchClientList();
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
        const programData = await clientApiService.fetchClientProgram(clientId);
        setClientProgram(programData);
      } catch (error) {
        console.error("Error fetching client program data:", error);
        Swal.fire("Error", "Failed to load program data", "error");
      }
    }
  };

  const handleGoalLinked = (domainId, goalId) => {
    console.log(domainId, goalId);
    setClientProgram((prevProgram) =>
      prevProgram.map((domain) => {
        if (domain.id === domainId) {
          const updatedGoals = domain.goals.map((goal) =>
            goal.id === goalId ? { ...goal, is_goal_linked: true } : goal
          );
          return { ...domain, goals: updatedGoals };
        }
        return domain;
      })
    );
  };

  const toggleExpandAll = () => setExpandAll((prev) => !prev);

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
                className="btn btn-sm btn-outline-secondary ms-2"
                onClick={toggleExpandAll}
              >
                {expandAll ? "Collapse All" : "Expand All"}
              </button>
            </div>
          </div>
          <div className="card-body">
            {clientProgram.map((domain) => (
              <ClientDomainComponent
                key={domain.id}
                domain={domain}
                clientId={selectedClientId}
                expandAll={expandAll}
                onGoalLinked={handleGoalLinked} // Pass the function down
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
    </div>
  );
};

export default App;
