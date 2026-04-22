const API_BASE = "http://127.0.0.1:5000/api";
const USER_ID = 1;

let allNotes = [];
let allTasks = [];

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);

    if (!response.ok) {
        const text = await response.text();
        throw new Error(text || `Request failed: ${response.status}`);
    }

    return response.json();
}

function formatDate(dateStr) {
    if (!dateStr) return "No date";
    return new Date(dateStr).toLocaleString();
}

function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

async function renderDashboard() {
    try {
        const data = await fetchJson(`${API_BASE}/dashboard?userId=${USER_ID}`);

        document.getElementById("totalNotes").textContent = data.totalNotes ?? 0;
        document.getElementById("totalHours").textContent = Number(data.totalHours ?? 0).toFixed(1);
        document.getElementById("todayHours").textContent = Number(data.todayHours ?? 0).toFixed(1);

        const streak = Number(data.studyStreak ?? 0);
        const longest = Number(data.longestStreak ?? 0);
        document.getElementById("studyStreak").textContent = `🔥 ${streak} day${streak === 1 ? "" : "s"}`;
        document.getElementById("longestStreak").textContent = `🏆 ${longest} day${longest === 1 ? "" : "s"}`;

        // Motivational message
        if (streak > 7) {
            document.getElementById("studyStreak").textContent += " - You're on fire! 🔥";
        }

        renderProgressList(data.progress || []);
        renderRecentNotes(data.recentNotes || []);
    } catch (error) {
        console.error("Dashboard load failed:", error);
        alert("Could not load dashboard.");
    }
}

function renderProgressList(progress) {
    const container = document.getElementById("progressList");
    container.innerHTML = "";

    if (!progress.length) {
        container.innerHTML = `<p class="empty">No task progress yet.</p>`;
        return;
    }

    progress.forEach(item => {
        const percent = Number(item.progressPercent || 0);

        const card = document.createElement("div");
        card.className = "progress-item";
        card.innerHTML = `
            <div class="progress-header">
                <strong>${escapeHtml(item.subject || "Unknown Subject")}</strong>
                <span>${item.completedTasks || 0}/${item.totalTasks || 0} tasks</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:${percent}%"></div>
            </div>
            <div class="progress-text">${percent.toFixed(1)}%</div>
        `;
        container.appendChild(card);
    });
}

function renderRecentNotes(notes) {
    const container = document.getElementById("recentNotes");
    container.innerHTML = "";

    if (!notes.length) {
        container.innerHTML = `<p class="empty">No recent notes.</p>`;
        return;
    }

    notes.forEach(note => {
        const div = document.createElement("div");
        div.className = "note-card";

        const content = note.content || "";
        div.innerHTML = `
            <h4>${escapeHtml(note.title)}</h4>
            <div class="note-meta">
                Subject: ${escapeHtml(note.subject || "None")} | ${formatDate(note.createdAt)}
            </div>
            <p>${escapeHtml(content.substring(0, 120))}${content.length > 120 ? "..." : ""}</p>
        `;
        container.appendChild(div);
    });
}

async function loadNotesFromServer() {
    try {
        allNotes = await fetchJson(`${API_BASE}/notes?userId=${USER_ID}`);
        renderNotes();
    } catch (error) {
        console.error("Loading notes failed:", error);
        allNotes = [];
        renderNotes();
        alert("Could not load notes.");
    }
}

async function saveNote() {
    const subject = document.getElementById("noteSubject").value.trim();
    const title = document.getElementById("noteTitle").value.trim();
    const content = document.getElementById("noteContent").value.trim();

    if (!title || !content) {
        alert("Title and content are required.");
        return;
    }

    try {
        await fetchJson(`${API_BASE}/notes`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                userId: USER_ID,
                subject,
                title,
                content,
                sourceType: "manual",
                fileName: null
            })
        });

        clearNoteFields();
        await loadNotesFromServer();
        await renderDashboard();
    } catch (error) {
        console.error("Saving note failed:", error);
        alert("Could not save note.");
    }
}

function clearNoteFields() {
    document.getElementById("noteSubject").value = "";
    document.getElementById("noteTitle").value = "";
    document.getElementById("noteContent").value = "";
}

async function deleteNote(id) {
    try {
        await fetchJson(`${API_BASE}/notes/${id}?userId=${USER_ID}`, {
            method: "DELETE"
        });

        await loadNotesFromServer();
        await renderDashboard();
    } catch (error) {
        console.error("Deleting note failed:", error);
        alert("Could not delete note.");
    }
}

function renderNotes() {
    const search = document.getElementById("searchInput").value.trim().toLowerCase();
    const subjectFilter = document.getElementById("filterSubject").value.trim().toLowerCase();

    const filtered = [...allNotes]
        .filter(note => {
            const title = (note.title || "").toLowerCase();
            const content = (note.content || "").toLowerCase();
            const subject = (note.subject || "").toLowerCase();

            return (title.includes(search) || content.includes(search)) &&
                (!subjectFilter || subject.includes(subjectFilter));
        })
        .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

    const list = document.getElementById("notesList");
    list.innerHTML = "";

    if (!filtered.length) {
        list.innerHTML = `<p class="empty">No matching notes found.</p>`;
        return;
    }

    filtered.forEach(note => {
        const div = document.createElement("div");
        div.className = "note-card";
        div.innerHTML = `
            <h4>${escapeHtml(note.title)}</h4>
            <div class="note-meta">
                Subject: ${escapeHtml(note.subject || "None")} |
                Source: ${escapeHtml(note.sourceType || "manual")} |
                ${formatDate(note.createdAt)}
            </div>
            <p>${escapeHtml(note.content || "").replace(/\n/g, "<br>")}</p>
            <div class="note-actions">
                <button onclick="deleteNote(${note.noteId})">Delete</button>
            </div>
        `;
        list.appendChild(div);
    });
}

async function saveStudySession() {
    const subject = document.getElementById("studySubject").value.trim();
    const minutes = Number(document.getElementById("studyMinutes").value.trim());
    const sessionNote = document.getElementById("studySessionNote").value.trim();

    if (!subject) {
        alert("Subject is required.");
        return;
    }

    if (!minutes || minutes <= 0) {
        alert("Enter valid study minutes.");
        return;
    }

    try {
        await fetchJson(`${API_BASE}/study-sessions`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                userId: USER_ID,
                subject,
                minutesStudied: minutes,
                sessionNote
            })
        });

        document.getElementById("studySubject").value = "";
        document.getElementById("studyMinutes").value = "";
        document.getElementById("studySessionNote").value = "";

        await renderDashboard();
    } catch (error) {
        console.error("Saving study session failed:", error);
        alert("Could not save study session.");
    }
}

async function loadTasksFromServer() {
    try {
        allTasks = await fetchJson(`${API_BASE}/tasks?userId=${USER_ID}`);
        renderTasks();
    } catch (error) {
        console.error("Loading tasks failed:", error);
        allTasks = [];
        renderTasks();
        alert("Could not load tasks.");
    }
}

async function saveTask() {
    const subject = document.getElementById("taskSubject").value.trim();
    const title = document.getElementById("taskTitle").value.trim();
    const description = document.getElementById("taskDescription").value.trim();
    const dueDate = document.getElementById("taskDueDate").value;
    const priority = document.getElementById("taskPriority").value;

    if (!title) {
        alert("Task title is required.");
        return;
    }

    try {
        await fetchJson(`${API_BASE}/tasks`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                userId: USER_ID,
                subject,
                title,
                description,
                dueDate,
                priority
            })
        });

        clearTaskFields();
        await loadTasksFromServer();
        await renderDashboard();
    } catch (error) {
        console.error("Saving task failed:", error);
        alert("Could not save task.");
    }
}

function clearTaskFields() {
    document.getElementById("taskSubject").value = "";
    document.getElementById("taskTitle").value = "";
    document.getElementById("taskDescription").value = "";
    document.getElementById("taskDueDate").value = "";
    document.getElementById("taskPriority").value = "medium";
}

async function toggleTask(taskId) {
    try {
        await fetchJson(`${API_BASE}/tasks/${taskId}/toggle?userId=${USER_ID}`, {
            method: "PUT"
        });

        await loadTasksFromServer();
        await renderDashboard();
    } catch (error) {
        console.error("Toggling task failed:", error);
        alert("Could not update task.");
    }
}

async function deleteTask(taskId) {
    try {
        await fetchJson(`${API_BASE}/tasks/${taskId}?userId=${USER_ID}`, {
            method: "DELETE"
        });

        await loadTasksFromServer();
        await renderDashboard();
    } catch (error) {
        console.error("Deleting task failed:", error);
        alert("Could not delete task.");
    }
}

function renderTasks() {
    const search = document.getElementById("taskSearchInput").value.trim().toLowerCase();
    const subjectFilter = document.getElementById("taskFilterSubject").value.trim().toLowerCase();

    const filtered = [...allTasks]
        .filter(task => {
            const title = (task.title || "").toLowerCase();
            const description = (task.description || "").toLowerCase();
            const subject = (task.subject || "").toLowerCase();

            return (title.includes(search) || description.includes(search)) &&
                (!subjectFilter || subject.includes(subjectFilter));
        })
        .sort((a, b) => {
            if (!!a.isCompleted !== !!b.isCompleted) {
                return a.isCompleted ? 1 : -1;
            }
            return new Date(b.createdAt) - new Date(a.createdAt);
        });

    const list = document.getElementById("tasksList");
    list.innerHTML = "";

    if (!filtered.length) {
        list.innerHTML = `<p class="empty">No tasks found.</p>`;
        return;
    }

    filtered.forEach(task => {
        const completed = !!task.isCompleted;
        const div = document.createElement("div");
        div.className = `task-card ${completed ? "completed" : "pending"}`;

        div.innerHTML = `
            <div class="badge ${completed ? "done" : "todo"}">
                ${completed ? "Completed" : "Pending"}
            </div>
            <div class="priority-badge priority-${task.priority || 'medium'}">
                ${getPriorityIcon(task.priority || 'medium')} ${capitalize(task.priority || 'medium')}
            </div>
            <h4>${escapeHtml(task.title)}</h4>
            <div class="task-meta">
                Subject: ${escapeHtml(task.subject || "None")} |
                Due: ${task.dueDate || "No due date"} |
                Created: ${formatDate(task.createdAt)}
            </div>
            <p>${escapeHtml(task.description || "")}</p>
            <div class="task-actions">
                <button onclick="toggleTask(${task.taskId})">
                    ${completed ? "Mark Pending" : "Mark Done"}
                </button>
                <button class="secondary" onclick="deleteTask(${task.taskId})">Delete</button>
            </div>
        `;

        list.appendChild(div);
    });
}

async function importSelectedFile() {
    const fileInput = document.getElementById("fileInput");
    const file = fileInput.files[0];

    if (!file) {
        alert("Please choose a file first.");
        return;
    }

    const fileName = file.name.toLowerCase();

    if (!fileName.endsWith(".txt") && !fileName.endsWith(".json")) {
        alert("Only .txt and .json files are supported.");
        fileInput.value = "";
        return;
    }

    const reader = new FileReader();

    reader.onload = async function (e) {
        const content = e.target.result;

        try {
            if (fileName.endsWith(".txt")) {
                await fetchJson(`${API_BASE}/notes`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        userId: USER_ID,
                        subject: document.getElementById("noteSubject").value.trim(),
                        title: file.name.replace(/\.[^/.]+$/, ""),
                        content: content,
                        sourceType: "import",
                        fileName: file.name
                    })
                });

                alert("TXT file imported successfully.");
            } else if (fileName.endsWith(".json")) {
                const imported = JSON.parse(content);

                if (!Array.isArray(imported)) {
                    alert("JSON file must contain an array of notes.");
                    fileInput.value = "";
                    return;
                }

                for (const item of imported) {
                    await fetchJson(`${API_BASE}/notes`, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            userId: USER_ID,
                            subject: item.subject || "",
                            title: item.title || "Imported Note",
                            content: item.content || "",
                            sourceType: "import",
                            fileName: file.name
                        })
                    });
                }

                alert("JSON file imported successfully.");
            }

            fileInput.value = "";
            await loadNotesFromServer();
            await renderDashboard();
        } catch (error) {
            console.error("Import failed:", error);
            alert("Import failed. Check the backend console and file format.");
        }
    };

    reader.onerror = function () {
        alert("Could not read the selected file.");
    };

    reader.readAsText(file);
}

function setupTabs() {
    const buttons = document.querySelectorAll(".tab-btn");
    const tabs = document.querySelectorAll(".tab-content");

    buttons.forEach(button => {
        button.addEventListener("click", () => {
            buttons.forEach(btn => btn.classList.remove("active"));
            tabs.forEach(tab => tab.classList.remove("active"));

            button.classList.add("active");
            document.getElementById(button.dataset.tab).classList.add("active");
        });
    });
}

function getPriorityIcon(priority) {
    switch (priority) {
        case 'high': return '🔴';
        case 'medium': return '🟡';
        case 'low': return '🟢';
        default: return '🟡';
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function initApp() {
    document.getElementById("saveNoteBtn").addEventListener("click", saveNote);
    document.getElementById("clearNoteBtn").addEventListener("click", clearNoteFields);
    document.getElementById("saveStudyBtn").addEventListener("click", saveStudySession);
    document.getElementById("saveTaskBtn").addEventListener("click", saveTask);
    document.getElementById("importFileBtn").addEventListener("click", importSelectedFile);

    document.getElementById("searchInput").addEventListener("input", renderNotes);
    document.getElementById("filterSubject").addEventListener("input", renderNotes);

    document.getElementById("taskSearchInput").addEventListener("input", renderTasks);
    document.getElementById("taskFilterSubject").addEventListener("input", renderTasks);

    setupTabs();

    Promise.all([
        loadNotesFromServer(),
        loadTasksFromServer()
    ]).then(() => renderDashboard());
}

window.deleteNote = deleteNote;
window.toggleTask = toggleTask;
window.deleteTask = deleteTask;

window.addEventListener("DOMContentLoaded", initApp);