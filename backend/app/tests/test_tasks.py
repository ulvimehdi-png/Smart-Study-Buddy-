def test_create_task(client, auth_headers, subject):
    response = client.post(
        "/tasks",
        headers=auth_headers,
        json={
            "subject_id": subject.id,
            "title": "Implement JWT auth",
            "description": "Add register and login routes",
            "status": "todo",
            "priority": "high",
            "estimated_minutes": 90,
        },
    )

    assert response.status_code == 201
    data = response.json()
    assert data["title"] == "Implement JWT auth"
    assert data["subject_id"] == subject.id
    assert data["priority"] == "high"


def test_list_tasks(client, auth_headers, task):
    response = client.get("/tasks", headers=auth_headers)

    assert response.status_code == 200
    data = response.json()
    assert len(data) == 1
    assert data[0]["title"] == "Implement auth API"


def test_get_task(client, auth_headers, task):
    response = client.get(f"/tasks/{task.id}", headers=auth_headers)

    assert response.status_code == 200
    data = response.json()
    assert data["id"] == task.id
    assert data["title"] == task.title


def test_update_task_to_done_sets_completed_at(client, auth_headers, task):
    response = client.put(
        f"/tasks/{task.id}",
        headers=auth_headers,
        json={
            "status": "done",
        },
    )

    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "done"
    assert data["completed_at"] is not None


def test_update_task_back_to_todo_clears_completed_at(client, auth_headers, task):
    client.put(
        f"/tasks/{task.id}",
        headers=auth_headers,
        json={"status": "done"},
    )

    response = client.put(
        f"/tasks/{task.id}",
        headers=auth_headers,
        json={"status": "todo"},
    )

    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "todo"
    assert data["completed_at"] is None


def test_create_task_with_invalid_subject_fails(client, auth_headers):
    response = client.post(
        "/tasks",
        headers=auth_headers,
        json={
            "subject_id": 999,
            "title": "Invalid subject task",
        },
    )

    assert response.status_code == 404
    assert response.json()["detail"] == "Subject not found"