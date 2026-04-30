const API = "http://127.0.0.1:5000/api";
const UID = 1;
let allNotes = [], allTasks = [], importBuffer = [], searchTimer = null;
let calYear, calMonth;

// ── Helpers ──
async function api(url, opts = {}) {
  const r = await fetch(url, opts);
  if (!r.ok) throw new Error(await r.text() || r.status);
  return r.json();
}
function esc(v) { return String(v ?? "").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;"); }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString("en-GB",{day:"numeric",month:"short",year:"numeric"}) : "—"; }
function highlight(text, q) {
  if (!q) return esc(text);
  const safe = esc(q).replace(/[.*+?^${}()|[\]\\]/g,"\\$&");
  return esc(text).replace(new RegExp(`(${safe})`,"gi"),'<mark class="search-highlight">$1</mark>');
}
function toast(msg, type="info") {
  const el = document.createElement("div");
  el.className = `toast ${type}`;
  el.textContent = msg;
  document.getElementById("toastContainer").appendChild(el);
  setTimeout(() => el.remove(), 3500);
}
function getPriorityIcon(p) { return {high:"🔴",medium:"🟡",low:"🟢"}[p]||"🟡"; }
function cap(s) { return s.charAt(0).toUpperCase()+s.slice(1); }
function todayStr() { return new Date().toISOString().slice(0,10); }

// ── Tabs ──
function setupTabs() {
  const btns = document.querySelectorAll(".tab-btn");
  const tabs = document.querySelectorAll(".tab-content");
  btns.forEach(b => b.addEventListener("click", () => {
    btns.forEach(x => x.classList.remove("active"));
    tabs.forEach(x => x.classList.remove("active"));
    b.classList.add("active");
    document.getElementById(b.dataset.tab).classList.add("active");
    if (b.dataset.tab === "calendarTab") renderCalendar();
  }));
}

// ── Dashboard ──
async function renderDashboard() {
  try {
    const d = await api(`${API}/dashboard?userId=${UID}`);
    document.getElementById("totalNotes").textContent = d.totalNotes ?? 0;
    document.getElementById("totalHours").textContent = Number(d.totalHours ?? 0).toFixed(1);
    document.getElementById("todayHours").textContent = Number(d.todayHours ?? 0).toFixed(1);
    const s = Number(d.studyStreak??0), l = Number(d.longestStreak??0);
    document.getElementById("studyStreak").textContent = `🔥 ${s} day${s===1?"":"s"}`;
    document.getElementById("longestStreak").textContent = `🏆 ${l} day${l===1?"":"s"}`;
    renderProgress(d.progress||[]);
    renderRecent(d.recentNotes||[]);
  } catch(e) { console.error(e); toast("Dashboard load failed","error"); }
}
function renderProgress(items) {
  const c = document.getElementById("progressList");
  if (!items.length) { c.innerHTML = '<p class="empty">No progress yet.</p>'; return; }
  c.innerHTML = items.map(i => {
    const p = Number(i.progressPercent||0);
    return `<div class="progress-item"><div class="progress-header"><strong>${esc(i.subject||"?")}</strong><span>${i.completedTasks||0}/${i.totalTasks||0}</span></div><div class="progress-bar"><div class="progress-fill" style="width:${p}%"></div></div><div class="progress-text">${p.toFixed(1)}%</div></div>`;
  }).join("");
}
function renderRecent(notes) {
  const c = document.getElementById("recentNotes");
  if (!notes.length) { c.innerHTML = '<p class="empty">No recent notes.</p>'; return; }
  c.innerHTML = notes.map(n => `<div class="note-card"><h4>${esc(n.title)}</h4><div class="note-meta"><span>${esc(n.subject||"None")}</span><span>${fmtDate(n.createdAt)}</span></div><p>${esc((n.content||"").substring(0,120))}${(n.content||"").length>120?"…":""}</p></div>`).join("");
}

// ── Notes ──
async function loadNotes() {
  try { allNotes = await api(`${API}/notes?userId=${UID}`); } catch(e) { allNotes = []; toast("Notes load failed","error"); }
  renderNotes();
}
async function saveNote() {
  const subj = document.getElementById("noteSubject").value.trim();
  const title = document.getElementById("noteTitle").value.trim();
  const content = document.getElementById("noteContent").value.trim();
  const editId = document.getElementById("editNoteId").value;
  if (!title || !content) { toast("Title and content required","error"); return; }
  try {
    if (editId) {
      await api(`${API}/notes/${editId}`, { method:"PUT", headers:{"Content-Type":"application/json"}, body: JSON.stringify({userId:UID,title,content}) });
      toast("Note updated","success");
    } else {
      await api(`${API}/notes`, { method:"POST", headers:{"Content-Type":"application/json"}, body: JSON.stringify({userId:UID,subject:subj,title,content,sourceType:"manual",fileName:null}) });
      toast("Note saved","success");
    }
    clearNoteFields(); await loadNotes(); await renderDashboard();
  } catch(e) { toast("Save failed","error"); }
}
function clearNoteFields() {
  ["noteSubject","noteTitle","noteContent"].forEach(id => document.getElementById(id).value = "");
  document.getElementById("editNoteId").value = "";
  document.getElementById("noteFormTitle").textContent = "Create Note";
}
async function deleteNote(id) {
  try { await api(`${API}/notes/${id}?userId=${UID}`,{method:"DELETE"}); toast("Note deleted","success"); await loadNotes(); await renderDashboard(); } catch(e) { toast("Delete failed","error"); }
}
function editNote(id) {
  const n = allNotes.find(x => x.noteId === id);
  if (!n) return;
  document.getElementById("editModalNoteId").value = id;
  document.getElementById("editModalTitle").value = n.title;
  document.getElementById("editModalContent").value = n.content;
  document.getElementById("editNoteModal").classList.remove("hidden");
}
function closeEditModal() { document.getElementById("editNoteModal").classList.add("hidden"); }
async function saveEditModal() {
  const id = document.getElementById("editModalNoteId").value;
  const title = document.getElementById("editModalTitle").value.trim();
  const content = document.getElementById("editModalContent").value.trim();
  if (!title||!content) { toast("Title and content required","error"); return; }
  try {
    await api(`${API}/notes/${id}`,{method:"PUT",headers:{"Content-Type":"application/json"},body:JSON.stringify({userId:UID,title,content})});
    toast("Note updated","success"); closeEditModal(); await loadNotes(); await renderDashboard();
  } catch(e) { toast("Update failed","error"); }
}

function renderNotes() {
  const q = document.getElementById("searchInput").value.trim().toLowerCase();
  const sf = document.getElementById("filterSubject").value.trim().toLowerCase();
  const filtered = allNotes.filter(n => {
    const t = (n.title||"").toLowerCase(), c = (n.content||"").toLowerCase(), s = (n.subject||"").toLowerCase();
    return (t.includes(q)||c.includes(q)) && (!sf||s.includes(sf));
  });
  const list = document.getElementById("notesList");
  if (!filtered.length) { list.innerHTML = '<p class="empty">No matching notes.</p>'; return; }
  list.innerHTML = filtered.map((n,i) => `<div class="note-card" style="animation-delay:${i*0.04}s"><h4>${highlight(n.title,q)}</h4><div class="note-meta"><span>${esc(n.subject||"None")}</span><span>${esc(n.sourceType||"manual")}</span><span>${fmtDate(n.createdAt)}</span></div><p>${highlight((n.content||"").substring(0,200),q)}${(n.content||"").length>200?"…":""}</p><div class="note-actions"><button class="btn-sm secondary" onclick="editNote(${n.noteId})">✏ Edit</button><button class="btn-sm danger" onclick="deleteNote(${n.noteId})">✕ Delete</button></div></div>`).join("");
}

// ── Tasks ──
async function loadTasks() {
  try { allTasks = await api(`${API}/tasks?userId=${UID}`); } catch(e) { allTasks = []; toast("Tasks load failed","error"); }
  renderTasks();
}
async function saveTask() {
  const subj = document.getElementById("taskSubject").value.trim();
  const title = document.getElementById("taskTitle").value.trim();
  const desc = document.getElementById("taskDescription").value.trim();
  const due = document.getElementById("taskDueDate").value;
  const pri = document.getElementById("taskPriority").value;
  if (!title) { toast("Task title required","error"); return; }
  try {
    await api(`${API}/tasks`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({userId:UID,subject:subj,title,description:desc,dueDate:due,priority:pri})});
    toast("Task saved","success"); clearTaskFields(); await loadTasks(); await renderDashboard();
  } catch(e) { toast("Save failed","error"); }
}
function clearTaskFields() { ["taskSubject","taskTitle","taskDescription","taskDueDate"].forEach(id=>document.getElementById(id).value=""); document.getElementById("taskPriority").value="medium"; }
async function toggleTask(id) { try { await api(`${API}/tasks/${id}/toggle?userId=${UID}`,{method:"PUT"}); await loadTasks(); await renderDashboard(); } catch(e) { toast("Toggle failed","error"); } }
async function deleteTask(id) { try { await api(`${API}/tasks/${id}?userId=${UID}`,{method:"DELETE"}); toast("Task deleted","success"); await loadTasks(); await renderDashboard(); } catch(e) { toast("Delete failed","error"); } }
function renderTasks() {
  const q = document.getElementById("taskSearchInput").value.trim().toLowerCase();
  const sf = document.getElementById("taskFilterSubject").value.trim().toLowerCase();
  const filtered = allTasks.filter(t => {
    const ti = (t.title||"").toLowerCase(), d = (t.description||"").toLowerCase(), s = (t.subject||"").toLowerCase();
    return (ti.includes(q)||d.includes(q)) && (!sf||s.includes(sf));
  }).sort((a,b) => (a.isCompleted===b.isCompleted?0:a.isCompleted?1:-1));
  const list = document.getElementById("tasksList");
  if (!filtered.length) { list.innerHTML = '<p class="empty">No tasks found.</p>'; return; }
  list.innerHTML = filtered.map((t,i) => {
    const done = !!t.isCompleted;
    return `<div class="task-card ${done?"completed":"pending"}" style="animation-delay:${i*0.04}s"><span class="badge ${done?"done":"todo"}">${done?"Done":"Pending"}</span><span class="priority-badge priority-${t.priority||"medium"}">${getPriorityIcon(t.priority||"medium")} ${cap(t.priority||"medium")}</span><h4>${esc(t.title)}</h4><div class="task-meta"><span>${esc(t.subject||"None")}</span><span>Due: ${t.dueDate||"—"}</span><span>${fmtDate(t.createdAt)}</span></div><p>${esc(t.description||"")}</p><div class="task-actions"><button class="btn-sm" onclick="toggleTask(${t.taskId})">${done?"↩ Reopen":"✓ Done"}</button><button class="btn-sm danger" onclick="deleteTask(${t.taskId})">✕</button></div></div>`;
  }).join("");
}

// ── Study Session ──
async function saveStudySession() {
  const subj = document.getElementById("studySubject").value.trim();
  const mins = Number(document.getElementById("studyMinutes").value);
  const note = document.getElementById("studySessionNote").value.trim();
  if (!subj) { toast("Subject required","error"); return; }
  if (!mins||mins<=0) { toast("Enter valid minutes","error"); return; }
  try {
    await api(`${API}/study-sessions`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({userId:UID,subject:subj,minutesStudied:mins,sessionNote:note})});
    toast("Session saved","success");
    ["studySubject","studyMinutes","studySessionNote"].forEach(id=>document.getElementById(id).value="");
    await renderDashboard();
  } catch(e) { toast("Save failed","error"); }
}

// ── Import / Export ──
function setupImport() {
  const drop = document.getElementById("dropZone");
  const inp = document.getElementById("fileInput");
  drop.addEventListener("click", () => inp.click());
  drop.addEventListener("dragover", e => { e.preventDefault(); drop.classList.add("dragover"); });
  drop.addEventListener("dragleave", () => drop.classList.remove("dragover"));
  drop.addEventListener("drop", e => { e.preventDefault(); drop.classList.remove("dragover"); if(e.dataTransfer.files.length) handleImportFile(e.dataTransfer.files[0]); });
  inp.addEventListener("change", () => { if(inp.files.length) handleImportFile(inp.files[0]); });
  document.getElementById("confirmImportBtn").addEventListener("click", confirmImport);
  document.getElementById("cancelImportBtn").addEventListener("click", cancelImport);
}

function handleImportFile(file) {
  const name = file.name.toLowerCase();
  if (!name.endsWith(".json") && !name.endsWith(".csv")) { toast("Only .json and .csv supported","error"); return; }
  const reader = new FileReader();
  reader.onload = e => {
    try {
      if (name.endsWith(".json")) {
        const data = JSON.parse(e.target.result);
        importBuffer = Array.isArray(data) ? data : [data];
      } else {
        importBuffer = parseCSV(e.target.result);
      }
      if (!importBuffer.length) { toast("No records found","error"); return; }
      showPreview(importBuffer);
    } catch(err) { toast("Parse error: " + err.message, "error"); }
  };
  reader.onerror = () => toast("Could not read file","error");
  reader.readAsText(file);
}

function parseCSV(text) {
  const lines = text.split(/\r?\n/).filter(l => l.trim());
  if (lines.length < 2) return [];
  const headers = lines[0].split(",").map(h => h.trim().replace(/^"|"$/g,""));
  return lines.slice(1).map(line => {
    const vals = line.match(/(".*?"|[^,]+)/g) || [];
    const obj = {};
    headers.forEach((h,i) => { obj[h] = (vals[i]||"").trim().replace(/^"|"$/g,""); });
    return obj;
  });
}

function showPreview(data) {
  document.getElementById("importPreview").classList.remove("hidden");
  document.getElementById("previewCount").textContent = data.length;
  const keys = Object.keys(data[0]);
  document.getElementById("previewHead").innerHTML = "<tr>" + keys.map(k => `<th>${esc(k)}</th>`).join("") + "</tr>";
  document.getElementById("previewBody").innerHTML = data.slice(0,50).map(row => "<tr>" + keys.map(k => `<td>${esc(row[k]||"")}</td>`).join("") + "</tr>").join("");
}

async function confirmImport() {
  let ok = 0, fail = 0;
  for (const item of importBuffer) {
    try {
      await api(`${API}/notes`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({userId:UID,subject:item.subject||"",title:item.title||"Imported",content:item.content||"",sourceType:"import",fileName:null})});
      ok++;
    } catch(e) { fail++; }
  }
  toast(`Imported ${ok} notes${fail?`, ${fail} failed`:""}`, fail?"error":"success");
  cancelImport(); await loadNotes(); await renderDashboard();
}
function cancelImport() {
  importBuffer = [];
  document.getElementById("importPreview").classList.add("hidden");
  document.getElementById("fileInput").value = "";
}

async function exportData(type, fmt) {
  try {
    const data = await api(`${API}/export/${type}?userId=${UID}`);
    const date = todayStr();
    let blob, filename;
    if (fmt === "json") {
      blob = new Blob([JSON.stringify(data,null,2)],{type:"application/json"});
      filename = `${type}_${date}.json`;
    } else {
      if (!data.length) { toast("No data to export","error"); return; }
      const keys = Object.keys(data[0]);
      const csv = [keys.join(","), ...data.map(r => keys.map(k => `"${String(r[k]??"").replace(/"/g,'""')}"`).join(","))].join("\n");
      blob = new Blob([csv],{type:"text/csv"});
      filename = `${type}_${date}.csv`;
    }
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url; a.download = filename; a.click();
    URL.revokeObjectURL(url);
    toast(`${cap(type)} exported as ${fmt.toUpperCase()}`,"success");
  } catch(e) { toast("Export failed","error"); }
}

// ── Calendar ──
function initCalendar() {
  const now = new Date();
  calYear = now.getFullYear(); calMonth = now.getMonth();
  document.getElementById("prevMonthBtn").addEventListener("click", () => { calMonth--; if(calMonth<0){calMonth=11;calYear--;} renderCalendar(); });
  document.getElementById("nextMonthBtn").addEventListener("click", () => { calMonth++; if(calMonth>11){calMonth=0;calYear++;} renderCalendar(); });
}

async function renderCalendar() {
  const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
  document.getElementById("calendarMonth").textContent = `${months[calMonth]} ${calYear}`;
  const first = new Date(calYear, calMonth, 1);
  const last = new Date(calYear, calMonth+1, 0);
  const startDay = (first.getDay()+6)%7;
  const startDate = new Date(first); startDate.setDate(startDate.getDate()-startDay);
  const endDate = new Date(last); endDate.setDate(endDate.getDate()+(6-(last.getDay()+6)%7));
  const startStr = startDate.toISOString().slice(0,10);
  const endStr = endDate.toISOString().slice(0,10);

  let sessions = [], tasks = [];
  try { sessions = await api(`${API}/calendar/sessions?userId=${UID}&start=${startStr}&end=${endStr}`); } catch(e) {}
  try { tasks = await api(`${API}/calendar/tasks?userId=${UID}&start=${startStr}&end=${endStr}`); } catch(e) {}

  const grid = document.getElementById("calendarGrid");
  const labels = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
  let html = labels.map(l => `<div class="calendar-day-label">${l}</div>`).join("");

  const today = todayStr();
  const cur = new Date(startDate);
  while (cur <= endDate) {
    const iso = cur.toISOString().slice(0,10);
    const isToday = iso === today;
    const isOther = cur.getMonth() !== calMonth;
    const daySessions = sessions.filter(s => (s.date||"").slice(0,10)===iso);
    const dayTasks = tasks.filter(t => (t.dueDate||"").slice(0,10)===iso);
    let eventsHtml = "";
    daySessions.slice(0,2).forEach(s => { eventsHtml += `<div class="calendar-event session">${s.minutes}m ${esc(s.subject||"")}</div>`; });
    dayTasks.slice(0,2).forEach(t => { eventsHtml += `<div class="calendar-event ${t.status==="done"?"task-done":"task-pending"}">${esc(t.title||"")}</div>`; });
    const extra = daySessions.length+dayTasks.length-4;
    if (extra > 0) eventsHtml += `<div class="calendar-event session">+${extra} more</div>`;
    html += `<div class="calendar-day${isToday?" today":""}${isOther?" other-month":""}" data-date="${iso}" onclick="openDayModal('${iso}')"><div class="day-number">${cur.getDate()}</div>${eventsHtml}</div>`;
    cur.setDate(cur.getDate()+1);
  }
  grid.innerHTML = html;
}

async function openDayModal(dateStr) {
  document.getElementById("dayModal").classList.remove("hidden");
  document.getElementById("dayModalTitle").textContent = new Date(dateStr+"T12:00:00").toLocaleDateString("en-GB",{weekday:"long",day:"numeric",month:"long",year:"numeric"});
  let sessions = [], tasks = [];
  try { sessions = await api(`${API}/calendar/sessions?userId=${UID}&start=${dateStr}&end=${dateStr}`); } catch(e) {}
  try { tasks = await api(`${API}/calendar/tasks?userId=${UID}&start=${dateStr}&end=${dateStr}`); } catch(e) {}
  let html = "";
  if (sessions.length) {
    html += "<h4 style='margin:12px 0 8px;font-size:14px;color:var(--primary)'>Study Sessions</h4>";
    sessions.forEach(s => { html += `<div class="note-card" style="margin-top:8px"><strong>${esc(s.subject||"")}</strong> — ${s.minutes} minutes</div>`; });
  }
  if (tasks.length) {
    html += "<h4 style='margin:16px 0 8px;font-size:14px;color:var(--accent-warning)'>Tasks Due</h4>";
    tasks.forEach(t => { html += `<div class="task-card ${t.status==="done"?"completed":"pending"}" style="margin-top:8px"><span class="badge ${t.status==="done"?"done":"todo"}">${t.status==="done"?"Done":"Pending"}</span><h4>${esc(t.title||"")}</h4><div class="task-meta"><span>${esc(t.subject||"")}</span><span>${cap(t.priority||"medium")}</span></div></div>`; });
  }
  if (!sessions.length && !tasks.length) html = '<p class="empty">Nothing on this day.</p>';
  document.getElementById("dayModalContent").innerHTML = html;
}
function closeDayModal() { document.getElementById("dayModal").classList.add("hidden"); }

// ── Init ──
function initApp() {
  setupTabs();
  document.getElementById("saveNoteBtn").addEventListener("click", saveNote);
  document.getElementById("clearNoteBtn").addEventListener("click", clearNoteFields);
  document.getElementById("saveStudyBtn").addEventListener("click", saveStudySession);
  document.getElementById("saveTaskBtn").addEventListener("click", saveTask);
  document.getElementById("editModalSaveBtn").addEventListener("click", saveEditModal);

  // Debounced search for notes
  document.getElementById("searchInput").addEventListener("input", () => { clearTimeout(searchTimer); searchTimer = setTimeout(renderNotes, 300); });
  document.getElementById("filterSubject").addEventListener("input", renderNotes);
  document.getElementById("taskSearchInput").addEventListener("input", renderTasks);
  document.getElementById("taskFilterSubject").addEventListener("input", renderTasks);

  // Close modals on overlay click
  document.getElementById("dayModal").addEventListener("click", e => { if(e.target===e.currentTarget) closeDayModal(); });
  document.getElementById("editNoteModal").addEventListener("click", e => { if(e.target===e.currentTarget) closeEditModal(); });

  setupImport();
  initCalendar();
  Promise.all([loadNotes(), loadTasks()]).then(() => renderDashboard());
}

// Globals
window.deleteNote = deleteNote;
window.editNote = editNote;
window.closeEditModal = closeEditModal;
window.toggleTask = toggleTask;
window.deleteTask = deleteTask;
window.exportData = exportData;
window.openDayModal = openDayModal;
window.closeDayModal = closeDayModal;
window.addEventListener("DOMContentLoaded", initApp);