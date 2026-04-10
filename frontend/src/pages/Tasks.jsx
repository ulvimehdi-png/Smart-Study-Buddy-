import React, { useState, useEffect } from 'react';

export default function Tasks() {
  const [tasks, setTasks] = useState([]);
  const [title, setTitle] = useState('');
  const [subject, setSubject] = useState('');
  const userId = 1; 

  const fetchTasks = () => {
    fetch(`http://127.0.0.1:8000/api/tasks?userId=${userId}`)
      .then(res => res.json())
      .then(data => setTasks(data))
      .catch(err => console.error("Error fetching tasks:", err));
  };

  useEffect(() => { fetchTasks(); }, []);

  const handleToggle = (taskId) => {
    fetch(`http://127.0.0.1:8000/api/tasks/${taskId}/toggle?userId=${userId}`, { method: 'PUT' })
      .then(() => fetchTasks());
  };

  const handleDelete = (taskId) => {
    fetch(`http://127.0.0.1:8000/api/tasks/${taskId}?userId=${userId}`, { method: 'DELETE' })
      .then(() => fetchTasks());
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title) return;
    fetch('http://127.0.0.1:8000/api/tasks', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ userId, title, subject })
    }).then(() => {
      setTitle('');
      setSubject('');
      fetchTasks();
    });
  };

  return (
    <div className="p-8 max-w-4xl mx-auto min-h-screen bg-gray-50">
      <h1 className="text-3xl font-bold mb-6 text-gray-800">My Tasks</h1>
      
      {/* Add Task Form */}
      <form onSubmit={handleSubmit} className="mb-8 flex gap-4 bg-white p-4 rounded shadow">
        <input 
          type="text" placeholder="Subject" value={subject}
          onChange={(e) => setSubject(e.target.value)}
          className="p-2 border rounded w-1/4"
        />
        <input 
          type="text" placeholder="What needs to be done?" value={title}
          onChange={(e) => setTitle(e.target.value)}
          className="p-2 border rounded flex-grow"
          required
        />
        <button className="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700">Add</button>
      </form>

      {/* Task List */}
      <div className="space-y-3">
        {tasks.map(task => (
          <div key={task.taskId} className="flex items-center justify-between bg-white p-4 rounded shadow border-l-4 border-blue-500">
            <div className="flex items-center gap-4">
              <input 
                type="checkbox" 
                checked={task.isCompleted} 
                onChange={() => handleToggle(task.taskId)}
                className="w-5 h-5 cursor-pointer"
              />
              <div>
                <p className={`font-semibold ${task.isCompleted ? 'line-through text-gray-400' : 'text-gray-800'}`}>
                  {task.title}
                </p>
                <span className="text-xs font-bold uppercase text-blue-500">{task.subject || 'General'}</span>
              </div>
            </div>
            <button onClick={() => handleDelete(task.taskId)} className="text-red-400 hover:text-red-600 text-sm font-bold">Delete</button>
          </div>
        ))}
      </div>
    </div>
  );
}