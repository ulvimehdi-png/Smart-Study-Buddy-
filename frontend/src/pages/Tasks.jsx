import React, { useState, useEffect } from 'react';

export default function Tasks() {
  const [tasks, setTasks] = useState([]);
  const [title, setTitle] = useState('');
  const userId = 1;

  useEffect(() => {
    fetch(`http://127.0.0.1:8000/api/tasks?userId=${userId}`)
      .then(res => res.json()).then(data => setTasks(data));
  }, []);

  const addTask = async (e) => {
    e.preventDefault();
    await fetch('http://127.0.0.1:8000/api/tasks', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ userId, title })
    });
    window.location.reload();
  };

  return (
    <div className="container" style={{padding: '20px'}}>
      <h1>My Tasks</h1>
      <form onSubmit={addTask} style={{marginBottom: '20px'}}>
        <input placeholder="New task..." value={title} onChange={e => setTitle(e.target.value)} required />
        <button type="submit">Add</button>
      </form>
      {tasks.map(t => (
        <div key={t.taskId} style={{padding: '10px', borderBottom: '1px solid #eee'}}>
          <span style={{textDecoration: t.isCompleted ? 'line-through' : 'none'}}>{t.title}</span>
        </div>
      ))}
    </div>
  );
}