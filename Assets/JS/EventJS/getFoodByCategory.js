function getMenuByCategory(
  menuContainerID,
  categories,
  categoryName,
  message,
  selectedItems = [],
  selectedIDs = []
) {
  const container = document.getElementById(menuContainerID);
  container.innerHTML = "";

  if (categories.length > 0) {
    categories.forEach((category) => {
      const wrapper = document.createElement("div");
      wrapper.classList.add("form-check");

      const input = document.createElement("input");
      input.name = categoryName + "Selections[]";
      input.type = "checkbox";
      input.id = category.foodItemID;
      input.value = category.foodName;
      input.classList.add("form-check-input");

      const label = document.createElement("label");
      label.setAttribute("for", input.id);
      label.textContent = category.foodName;
      label.classList.add("form-check-label");

      if (selectedItems.map(String).includes(String(category.foodName))) {
        input.checked = true;
      }

      if (selectedIDs.includes(String(category.foodItemID))) {
        input.checked = true;
      }

      wrapper.appendChild(input);
      wrapper.appendChild(label);
      container.appendChild(wrapper);
    });
  } else {
    const p = document.createElement("p");
    p.classList.add("card-text");
    p.textContent = message;
    container.appendChild(p);
  }
}
