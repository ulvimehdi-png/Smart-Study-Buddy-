import React, { useEffect, useState } from "react";
import { ChevronLeft, ChevronRight, Clock, BookText, CheckCircle, Circle } from "lucide-react";
import PageBackdrop from "../components/shared/PageBackdrop";

const DAYS = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const MONTHS = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

const formatDate = (date) => {
  return new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(date));
};

export default function Calendar() {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [calendarData, setCalendarData] = useState({ sessions: [], tasks: [] });
  const [selectedDate, setSelectedDate] = useState(null);
  const [loading, setLoading] = useState(true);

  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();

  const fetchCalendarData = () => {
    setLoading(true);
    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month + 1, 0);
    
    const startStr = startDate.toISOString().split("T")[0];
    const endStr = endDate.toISOString().split("T")[0];

    Promise.all([
      fetch(`/api/calendar/sessions?start=${startStr}&end=${endStr}`, { credentials: "include" })
        .then(res => res.json()),
      fetch(`/api/calendar/tasks?start=${startStr}&end=${endStr}`, { credentials: "include" })
        .then(res => res.json())
    ])
      .then(([sessionsData, tasksData]) => {
        setCalendarData({
          sessions: sessionsData,
          tasks: tasksData
        });
      })
      .catch(err => console.error("Error fetching calendar data:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchCalendarData();
  }, [year, month]);

  const getDaysInMonth = () => {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    const days = [];
    
    // Previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
      days.push({
        day: daysInPrevMonth - i,
        isCurrentMonth: false,
        date: new Date(year, month - 1, daysInPrevMonth - i)
      });
    }
    
    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
      days.push({
        day: i,
        isCurrentMonth: true,
        date: new Date(year, month, i)
      });
    }
    
    // Next month days
    const remaining = 42 - days.length;
    for (let i = 1; i <= remaining; i++) {
      days.push({
        day: i,
        isCurrentMonth: false,
        date: new Date(year, month + 1, i)
      });
    }
    
    return days;
  };

  const getEventsForDate = (date) => {
    const dateStr = date.toISOString().split("T")[0];
    const sessions = calendarData.sessions.filter(s => {
      const sessionDate = s.date?.split("T")[0];
      return sessionDate === dateStr;
    });
    const tasks = calendarData.tasks.filter(t => {
      const dueDate = t.dueDate?.split("T")[0];
      return dueDate === dateStr;
    });
    return { sessions, tasks };
  };

  const navigateMonth = (delta) => {
    setCurrentDate(new Date(year, month + delta, 1));
    setSelectedDate(null);
  };

  const goToToday = () => {
    setCurrentDate(new Date());
    setSelectedDate(new Date());
  };

  const days = getDaysInMonth();
  const today = new Date();
  const todayStr = today.toISOString().split("T")[0];

  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-7xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <Clock className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-2xl font-semibold sm:text-3xl">Calendar</h1>
              <p className="mt-1 text-sm text-text-secondary">
                View your study sessions and tasks.
              </p>
            </div>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
          {/* Calendar Grid */}
          <div className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
            {/* Month Navigation */}
            <div className="mb-6 flex items-center justify-between">
              <h2 className="text-xl font-semibold">
                {MONTHS[month]} {year}
              </h2>
              <div className="flex items-center gap-2">
                <button
                  onClick={goToToday}
                  className="rounded-lg px-3 py-1.5 text-sm font-medium text-primary hover:bg-primary/10"
                >
                  Today
                </button>
                <button
                  onClick={() => navigateMonth(-1)}
                  className="rounded-lg p-2 hover:bg-bg-elevated"
                >
                  <ChevronLeft className="h-5 w-5" />
                </button>
                <button
                  onClick={() => navigateMonth(1)}
                  className="rounded-lg p-2 hover:bg-bg-elevated"
                >
                  <ChevronRight className="h-5 w-5" />
                </button>
              </div>
            </div>

            {/* Day Headers */}
            <div className="mb-2 grid grid-cols-7 gap-1">
              {DAYS.map(day => (
                <div key={day} className="py-2 text-center text-sm font-medium text-text-muted">
                  {day}
                </div>
              ))}
            </div>

            {/* Calendar Days */}
            <div className="grid grid-cols-7 gap-1">
              {days.map((dayInfo, index) => {
                const dateStr = dayInfo.date.toISOString().split("T")[0];
                const isToday = dateStr === todayStr;
                const isSelected = selectedDate && selectedDate.toISOString().split("T")[0] === dateStr;
                const { sessions, tasks } = getEventsForDate(dayInfo.date);
                const hasEvents = sessions.length > 0 || tasks.length > 0;

                return (
                  <button
                    key={index}
                    onClick={() => setSelectedDate(dayInfo.date)}
                    className={`
                      relative min-h-[80px] rounded-lg p-2 text-left transition
                      ${!dayInfo.isCurrentMonth ? "text-text-muted" : "text-text-main"}
                      ${isToday ? "bg-primary/10" : ""}
                      ${isSelected ? "ring-2 ring-primary" : "hover:bg-bg-elevated"}
                    `}
                  >
                    <span className={`text-sm font-medium ${isToday ? "text-primary" : ""}`}>
                      {dayInfo.day}
                    </span>
                    
                    {/* Event Indicators */}
                    {hasEvents && (
                      <div className="mt-1 flex flex-wrap gap-1">
                        {sessions.length > 0 && (
                          <span className="inline-block h-1.5 w-1.5 rounded-full bg-accent" />
                        )}
                        {tasks.filter(t => t.status === "done").length > 0 && (
                          <span className="inline-block h-1.5 w-1.5 rounded-full bg-green-500" />
                        )}
                        {tasks.filter(t => t.status !== "done").length > 0 && (
                          <span className="inline-block h-1.5 w-1.5 rounded-full bg-amber-500" />
                        )}
                      </div>
                    )}
                  </button>
                );
              })}
            </div>
          </div>

          {/* Selected Date Details */}
          <div className="rounded-3xl border border-border-base bg-bg-card p-5 shadow-sm lg:sticky lg:top-24 lg:self-start">
            <h3 className="mb-4 text-lg font-semibold">
              {selectedDate ? formatDate(selectedDate) : "Select a date"}
            </h3>
            
            {selectedDate ? (
              <div className="space-y-4">
                {/* Sessions */}
                <div>
                  <h4 className="mb-2 flex items-center gap-2 text-sm font-medium text-text-secondary">
                    <Clock className="h-4 w-4" />
                    Study Sessions
                  </h4>
                  {getEventsForDate(selectedDate).sessions.length === 0 ? (
                    <p className="text-sm text-text-muted">No sessions</p>
                  ) : (
                    <ul className="space-y-2">
                      {getEventsForDate(selectedDate).sessions.map(session => (
                        <li key={session.id} className="rounded-lg bg-bg-elevated p-3">
                          <p className="text-sm font-medium">{session.subject}</p>
                          <p className="text-xs text-text-muted">{session.minutes} minutes</p>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>

                {/* Tasks */}
                <div>
                  <h4 className="mb-2 flex items-center gap-2 text-sm font-medium text-text-secondary">
                    <CheckCircle className="h-4 w-4" />
                    Tasks
                  </h4>
                  {getEventsForDate(selectedDate).tasks.length === 0 ? (
                    <p className="text-sm text-text-muted">No tasks due</p>
                  ) : (
                    <ul className="space-y-2">
                      {getEventsForDate(selectedDate).tasks.map(task => (
                        <li key={task.id} className="flex items-center gap-2 rounded-lg bg-bg-elevated p-3">
                          {task.status === "done" ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                          ) : (
                            <Circle className="h-4 w-4 text-amber-500" />
                          )}
                          <span className={`text-sm ${task.status === "done" ? "line-through text-text-muted" : ""}`}>
                            {task.title}
                          </span>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              </div>
            ) : (
              <p className="text-sm text-text-muted">
                Click on a day to see details.
              </p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}