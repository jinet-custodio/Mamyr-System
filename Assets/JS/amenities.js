// Function to show and hide amenities
function showAmenity(category) {
  // First, hide all containers
  const allContainers = document.querySelectorAll(
    ".amenity-wrapper .amenity-categories div"
  );

  // Hide all amenity content containers (like pool, cottage, etc.)
  const allAmenityContainers = document.querySelectorAll('[id$="Container"]'); // Select all containers by id suffix 'Container'
  allAmenityContainers.forEach((container) => {
    container.style.display = "none"; // Hide all amenity containers initially
  });

  // Show the relevant category container
  switch (category) {
    case "all":
      document.getElementById("videoContainer").style.display = "block";
      document.getElementById("poolContainer").style.display = "block";
      document.getElementById("cottageContainer").style.display = "block";
      document.getElementById("videokeContainer").style.display = "block";
      document.getElementById("pavilionContainer").style.display = "block";
      document.getElementById("minipavContainer").style.display = "block";
      document.getElementById("hotelContainer").style.display = "block";
      document.getElementById("parkingContainer").style.display = "block";
      break;
    case "pool":
      document.getElementById("pool-amenity").style.display = "block";
      document.getElementById("poolContainer").style.display = "block";
      break;
    case "cottage":
      document.getElementById("cottage-amenity").style.display = "block";
      document.getElementById("cottageContainer").style.display = "block";
      break;
    case "videoke":
      document.getElementById("videoke-amenity").style.display = "block";
      document.getElementById("videokeContainer").style.display = "block";
      break;
    case "pavilion":
      document.getElementById("pavilion-amenity").style.display = "block";
      document.getElementById("pavilionContainer").style.display = "block";
      break;
    case "minipav":
      document.getElementById("minipav-amenity").style.display = "block";
      document.getElementById("minipavContainer").style.display = "block";
      break;
    case "hotel":
      document.getElementById("hotel-amenity").style.display = "block";
      document.getElementById("hotelContainer").style.display = "block";
      break;
    case "parking":
      document.getElementById("parking-amenity").style.display = "block";
      document.getElementById("parkingContainer").style.display = "block";
      break;
    default:
      console.error("Unknown category:", category);
      break;
  }
}
