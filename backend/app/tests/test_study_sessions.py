def test_start_session(client, auth_headers, subject, task):
    response = client.post(
        "/study-sessions/start",
        headers=auth_headers,
        json={
            "subject_id": subject.id,
            "task_id": task.id,
        },
    )

    assert response.status_code == 201
    data = response.json()
    assert data["subject_id"] == subject.id
    assert data["task_id"] == task.id
    assert data["ended_at"] is None


def test_cannot_start_second_active_session(client, auth_headers, subject):
    first = client.post(
        "/study-sessions/start",
        headers=auth_headers,
        json={"subject_id": subject.id},
    )
    assert first.status_code == 201

    second = client.post(
        "/study-sessions/start",
        headers=auth_headers,
        json={"subject_id": subject.id},
    )

    assert second.status_code == 400
    assert second.json()["detail"] == "You already have an active study session"


def test_end_session(client, auth_headers, subject):
    start_response = client.post(
        "/study-sessions/start",
        headers=auth_headers,
        json={"subject_id": subject.id},
    )
    session_id = start_response.json()["id"]

    end_response = client.post(
        f"/study-sessions/{session_id}/end",
        headers=auth_headers,
        json={},
    )

    assert end_response.status_code == 200
    data = end_response.json()
    assert data["ended_at"] is not None
    assert data["actual_focus_minutes"] is not None


def test_get_active_session(client, auth_headers, subject):
    client.post(
        "/study-sessions/start",
        headers=auth_headers,
        json={"subject_id": subject.id},
    )

    response = client.get("/study-sessions/active", headers=auth_headers)

    assert response.status_code == 200
    data = response.json()
    assert data is not None
    assert data["ended_at"] is None