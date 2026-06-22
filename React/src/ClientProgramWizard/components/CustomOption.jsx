// React/src/ClientProgramWizard/components/CustomOption.jsx
const CustomOption = (props) => {
  const { children, innerRef, innerProps, isFocused, isSelected } = props;

  return (
    <div
      ref={innerRef}
      {...innerProps}
      style={{
        backgroundColor: isSelected
          ? "#2074BA"
          : isFocused
          ? "#e9ecef"
          : "#fff",
        color: isSelected ? "#fff" : "#000",
        padding: "10px",
        borderBottom: "1px solid #ccc", // Add border to each option
        margin: "0",
        
      }}
    >
      {children}
    </div>
  );
};

export default CustomOption;
