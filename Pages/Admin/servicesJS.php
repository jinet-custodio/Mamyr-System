<!-- For Hotel -->
<div class="hotelContainer" id="hotelContainer" style="display: none;">
    <button class="btn btn-primary" id="addHotelServiceBtn" onclick="addService()">Add a Service</button>
    <table class=" table table-striped" id="hotelServices">
        <thead>
            <th scope="col">Service Name</th>
            <th scope="col">Price</th>
            <th scope="col">Capacity</th>
            <th scope="col">Duration</th>
            <th scope="col">Description</th>
            <th scope="col">Image</th>
            <th scope="col">Availability</th>
            <th scope="col">Action</th>

        </thead>

        <tbody>
            <tr>
                <td><input type="text" class="form-control" id="hotelServiceName"></td>
                <td><input type="text" class="form-control" id="hotelServicePrice"></td>
                <td><input type="text" class="form-control" id="hotelServiceCapacity"></td>
                <td><input type="text" class="form-control" id="hotelServiceDuration"></td>
                <td><input type="text" class="form-control" id="hotelServiceDesc"></td>
                <td><input type="text" class="form-control" id="hotelServiceImage"></td>
                <td> <select id="hotelAvailability" name="hotelAvailability" class="form-select" required>
                        <option value="" disabled selected>Select Availability</option>
                        <option value="available" id="available">Available</option>
                        <option value="occupied" id="available">Occupied</option>
                        <option value="reserved" id="reserved">Reserved</option>
                        <option value="maintenance" id="maintenance">Maintenance</option>
                    </select>
                </td>
                <td class="buttonContainer">
                    <button class="btn btn-primary" id="editHotelService" onclick="edit()">Edit</button>
                    <button class="btn btn-danger deleteBtn" id="deleteHotelService">Delete</button>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="saveBtnContainer" id="saveBtnHotelContainer">
        <button type="submit" class="btn btn-success" id="saveChanges" onclick="saveButton()">Save</button>
    </div>
</div>


<script>
    const saveBtnResortContainer = document.getElementById("saveBtnResortContainer")
    const saveBtnHotelContainer = document.getElementById("saveBtnHotelContainer")
    const saveBtnEventContainer = document.getElementById("saveBtnEventContainer")
    const saveBtnCateringContainer = document.getElementById("saveBtnCateringContainer")

    saveBtnResortContainer.style.display = "none"
    saveBtnHotelContainer.style.display = "none"
    saveBtnEventContainer.style.display = "none"
    saveBtnCateringContainer.style.display = "none"


    function addService() {
        if (saveBtnResortContainer.style.display == "none") {
            saveBtnResortContainer.style.display = "flex";
            saveBtnHotelContainer.style.display = "none";
            saveBtnEventContainer.style.display = "none";
            saveBtnCateringContainer.style.display = "none";

        } else if (saveBtnHotelContainer.style.display == "none") {
            saveBtnHotelContainer.style.display = "flex";
            saveBtnResortContainer.style.display = "none";
            saveBtnEventContainer.style.display = "none";
            saveBtnCateringContainer.style.display = "none";

        } else if (saveBtnEventContainer.style.display == "none") {
            saveBtnEventContainer.style.display = "flex";
            saveBtnResortContainer.style.display = "none";
            saveBtnHotelContainer.style.display = "none";
            saveBtnCateringContainer.style.display = "none";
        } else if (saveBtnCateringContainer.style.display == "none") {
            saveBtnCateringContainer.style.display = "flex";
            saveBtnResortContainer.style.display = "none";
            saveBtnHotelContainer.style.display = "none";
            saveBtnEventContainer.style.display = "none";
        } else {
            saveBtnContainer.style.display = "flex"
        }
    }

    function edit() {
        if (saveBtnResortContainer.style.display == "none" || saveBtnHotelContainer.style.display == "none" ||
            saveBtnEventContainer.style.display == "none" || saveBtnCateringContainer.style.display == "none") {
            saveBtnResortContainer.style.display = "flex";
            saveBtnHotelContainer.style.display = "flex";
            saveBtnEventContainer.style.display = "flex";
            saveBtnCateringContainer.style.display = "flex";

        } else {
            saveBtnContainer.style.display = "flex"
        }
    }
</script>

<!-- For Resort Services -->
<script>
    // $(document).ready(function() {
    //     const table = $('#resortServices').DataTable();

    //     $('#addResortServiceBtn').on('click', function() {
    //         const newResortData = [
    //             '<input type="text" class="form-control" id="resortServiceName">',
    //             '<input type="text" class="form-control" id="resortServicePrice">',
    //             '<input type="text" class="form-control" id="resortServiceCapacity">',
    //             '<input type="text" class="form-control" id="resortServiceDuration">',
    //             '<input type="text" class="form-control" id="resortServiceDesc">',
    //             '<input type="text" class="form-control" id="resortServiceImage">',

    //             `<select id="resortAvailability" name="resortAvailability" class="form-select" required>
    //                         <option value="" disabled selected>Select Availability</option>
    //                         <option value="available" id="available">Available</option>
    //                         <option value="occupied" id="available">Occupied</option>
    //                         <option value="reserved" id="reserved">Reserved</option>
    //                         <option value="maintenance" id="maintenance">Maintenance</option>
    //                     </select>`,

    //             `<td class="buttonContainer">
    //                     <button class="btn btn-primary"  id="editResortService" onclick="edit()">Edit</button>
    //                     <button class="btn btn-danger deleteBtn"  id="deleteResortService">Delete</button>
    //                 </td>`,

    //         ];
    //         table.row.add(newResortData).draw(false);

    //     });

    // });
    $(document).ready(function() {
        const table = $('#resortServices').DataTable();

        $('#addResortServiceBtn').on('click', function() {
            const newResortData = [
                '<input type="text" name="resortServiceName[]" class="form-control resortServiceName">',
                '<input type="text" name="resortServicePrice[]" class="form-control resortServicePrice">',
                '<input type="text"  class="form-control resortServiceCapacity">',
                '<input type="text" class="form-control resortServiceDuration">',
                '<input type="text" class="form-control resortServiceDesc">',
                '<input type="text" class="form-control resortServiceImage">',

                `<select class="form-select resortAvailability" required>
                        <option value="" disabled selected>Select Availability</option>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                        <option value="maintenance">Maintenance</option>
                    </select>`,

                `<td class="buttonContainer">
                        <button class="btn btn-primary editResortService">Edit</button>
                        <button class="btn btn-danger deleteResortService">Delete</button>
                    </td>`,
            ];

            table.row.add(newResortData).draw(false);
        });


        $('#resortServices tbody').on('click', '.editResortService', function() {
            edit();
        });

    });
</script>

<script>
    $(document).ready(function() {
        const table = $('#hotelServices').DataTable();

        $(addHotelServiceBtn).on('click', function() {
            const newHotelData = [
                '<input type="text" class="form-control" id="hotelServiceName">',
                '<input type="text" class="form-control" id="hotelServicePrice">',
                '<input type="text" class="form-control" id="hotelServiceCapacity">',
                '<input type="text" class="form-control" id="hotelServiceDuration">',
                '<input type="text" class="form-control" id="hotelServiceDesc">',
                '<input type="text" class="form-control" id="hotelServiceImage">',

                `<select id="hotelAvailability" name="hotelAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editHotelService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteHotelService">Delete</button>
                        </td>`,

            ];
            table.row.add(newHotelData).draw(false);

        });

    });
</script>

<script>
    $(document).ready(function() {
        const table = $('#eventServices').DataTable();

        $(addEventServiceBtn).on('click', function() {
            const newEventData = [
                '<input type="text" class="form-control" id="EventServiceName">',
                '<input type="text" class="form-control" id="EventServicePrice">',
                '<input type="text" class="form-control" id="EventServiceCapacity">',
                '<input type="text" class="form-control" id="EventServiceDuration">',
                '<input type="text" class="form-control" id="EventServiceDesc">',
                '<input type="text" class="form-control" id="EventServiceImage">',

                `<select id="EventAvailability" name="EventAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editEventService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteEventService" >Delete</button>
                        </td>`,

            ];
            table.row.add(newEventData).draw(false);

        });

    });
</script>

<script>
    $(document).ready(function() {
        const table = $('#cateringServices').DataTable();

        $(addCateringServiceBtn).on('click', function() {
            const newCateringData = [
                '<input type="text" class="form-control" id="foodName">',
                '<input type="text" class="form-control" id="foodPrice">',
                '<input type="text" class="form-control" id="foodCategory">',


                ` <td> <select id="foodAvailability" name="foodAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="unavailable" id="unavailable">Unavailable</option>

                            </select>
                        </td>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editCateringService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteCateringService">Delete</button>
                        </td>`,

            ];
            table.row.add(newCateringData).draw(false);

        });

    });
</script>



<!-- Button Function -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const serviceCategories = document.getElementById("serviceCategories");
        const resortLink = document.getElementById("resort-link");
        const eventLink = document.getElementById("event-link");
        const cateringLink = document.getElementById("catering-link");
        const resortContainer = document.getElementById("resortContainer");
        const eventContainer = document.getElementById("eventContainer");
        const cateringContainer = document.getElementById("cateringContainer");
        const backButton = document.getElementById("backArrowContainer");

        backButton.addEventListener("click", function() {
            backButton.style.display = "none";
            resortContainer.style.display = "none";
            eventContainer.style.display = "none";
            cateringContainer.style.display = "none";
            document.getElementById("headerText").innerHTML = "Services";
            serviceCategories.style.display = "flex";
            document.body.setAttribute("style", "background-color: #a1c8c7")
        })

        resortLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            resortContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Resort";
            document.body.setAttribute("style", "background-color: whitesmoke;");
        });

        eventLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            eventContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Event"
            document.body.setAttribute("style", "background-color: whitesmoke;");
        })

        cateringLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            cateringContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Catering"
            document.body.setAttribute("style", "background-color: whitesmoke;");
        })
    });
</script>