/**
/**
 * Robust CSV parser that handles quoted values and escaped quotes.
 * @param {string} csvText - The raw CSV content.
 * @returns {Array<Object>} - Array of objects mapped by headers.
 */
export function parseCSV(csvText) {
    const lines = csvText.split(/\r?\n/).filter(line => line.trim());
    if (lines.length < 2) return [];

    // Parse headers
    const headers = parseCSVLine(lines[0]);

    return lines.slice(1).map(line => {
        const values = parseCSVLine(line);
        const obj = {};
        headers.forEach((header, index) => {
            obj[header] = values[index] !== undefined ? values[index] : "";
        });
        return obj;
    });
}

/**
 * Parses a single CSV line into an array of values.
 */
function parseCSVLine(line) {
    const result = [];
    let cur = "";
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        const next = line[i + 1];

        if (char === '"') {
            if (inQuotes && next === '"') {
                // Escaped quote ""
                cur += '"';
                i++;
            } else {
                // Toggle quote state
                inQuotes = !inQuotes;
            }
        } else if (char === ',' && !inQuotes) {
            // New field
            result.push(cur.trim());
            cur = "";
        } else {
            cur += char;
        }
    }
    result.push(cur.trim());
    return result;
}

/**
 * Maps task fields from import data to API expected fields.
 */
function normalizeTaskDueDate(value) {
    if (!value) return null;

    const text = String(value).trim();
    if (!text) return null;

    if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
        return `${text}T23:59:00`;
    }

    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(text)) {
        return text.replace(" ", "T");
    }

    return text;
}

export function mapTaskData(raw) {
    const subject = raw.subject || raw.Subject || "";
    const priority = String(raw.priority || raw.Priority || "medium").toLowerCase();
    const dueDate =
        raw.dueDate ||
        raw.dueAt ||
        raw.due_at ||
        raw["Due Date"] ||
        raw["Due date"] ||
        raw.date ||
        null;

    return {
        title: raw.title || raw.Title || "Imported Task",
        subject: typeof subject === "object" ? subject?.name || "" : subject,
        priority: ["low", "medium", "high"].includes(priority) ? priority : "medium",
        dueDate: normalizeTaskDueDate(dueDate),
        description: raw.description || raw.Description || ""
    };
}

/**
 * Maps note fields from import data to API expected fields.
 */
export function mapNoteData(raw) {
    return {
        title: raw.title || raw.Title || "Imported Note",
        content: raw.content || raw.Content || "",
        subject: raw.subject || raw.Subject || "",
        createdAt: raw.createdAt || raw.date || raw['Created At'] || null
    };
}
