// Modifies the reservation page to have a back arrow or home
async function reserveDoc(roomURL, newResURL) {
    return;
}

// Schedule with available and unavailable reservation times for the roomURL
async function scheduleDoc(roomURL) {
    console.log("Fetching room HTML document...");
    const calendarDoc = await fetchAndParseURL(roomURL);
    if (calendarDoc === null){
        console.error("Error: Calendar document is empty!");
        return;
    }
    console.log("Received room HTML document...");
    const calendarForm = calendarDoc.getElementById('#lc-reserve-room-openings');
    console.log("Writing new document...");
    document.body.innerHTML = calendarForm;
    console.log("Returning...");

    /*
    Archiving just in case I need it later. Realized I didn't need to create a whole new document, just swap the current body.
    // Create a new document to hold the reservation times
    console.log("Creating new document...");
    let newDoc = document.prototype.createHTMLDocument();
    newDoc.body.style.width = "100%";
    newDoc.body.style.overflow = 'scroll';

    // Append the form to the body of the document
    console.log("Adding calendar form to document...");
    const clonedForm = calendarForm.cloneNode(true);
    body.appendChild(clonedForm);
    */

    /*
    // TODO: Add event listener to refresh page with new reservationDoc
    newDoc.addEventListener('click', function(e) {
        e.preventDefault();

        // Go to next date, previous date, or specified date
        if (e.target.id === 'edit-last' || e.target.id === 'edit-next' || e.target.id === 'edit-submit') {
            const newRoomURL = e.target.href;
            return reservationDoc(newRoomURL);
        }

        // If button is make reservation
        else {
            const newResURL = e.target.href;
            return reserveDoc(roomURL, newResURL);
        }

    })
    */

    return;
}

// Function to fetch the webpage
async function fetchAndParseURL(roomURL) {
    // Try to fetch data from URL
    try {
        const response = await fetch(roomURL);

        if(!response.okay){
            throw Error(`HTTP error! status: ${response.status}`)
        }
        console.log("Response is okay...");

        const htmlString = await response.text();
        const parser = new DOMParser();
        
        // Parse string and return html as a document
        console.log("Returning fetched HTML document...");
        return parser.parseFromString(htmlString, 'text/html');

    } catch (e) {
        console.error('Failed to fetch or parse page:', e);
        return null;
    }
}