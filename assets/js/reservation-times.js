

// Retrieves the reservation times and returns it in an iframe document
async function reservationDoc(room) {
    const calendarDoc = await fetchAndParseURL(room);
    const calendarForm = calendarDoc.getElementById('#lc-reserve-room-openings');

    // Create a new document to hold the reservation times
    let newDoc = DOMImplementation.prototype.createHTMLDocument()
    newDoc.body.style.width = "100%";
    newDoc.body.style.height = "500px";
    newDoc.body.style.overflow = 'scroll';

    // Append the form to the body of the document
    const clonedForm = calendarForm.cloneNode(true);
    body.appendChild(clonedForm);

    return newDoc;
}

// Function to fetch the webpage
async function fetchAndParseURL(room) {
    let roomURL = 'https://calendar.salinapubliclibrary.org/reserve-room/' + room;

    // Try to fetch data from URL
    try {
        const response = await fetch(roomURL);

        if(!response.okay){
            throw Error(`HTTP error! status: ${response.status}`)
        }

        const htmlString = await response.text();
        const parser = new DOMParser();
        
        // Parse string and return html as a document
        return parser.parseFromString(htmlString, 'text/html')

    } catch (e) {
        console.error('Failed to fetch or parse page:', e);
    }
}