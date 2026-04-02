def test_create_subject(client, auth_headers):
    response = client.post(
        "/subjects",
        headers=auth_headers,
        json={
            "code": "SWT",
            "name": "Software and Web Technology",
            "description": "Main course project",
            "target_total_minutes": 3000,
            "target_weekly_minutes": 600,
        },
    )

    assert response.status_code == 201
    data = response.json()
    assert data["code"] == "SWT"
    assert data["name"] == "Software and Web Technology"
    assert data["target_weekly_minutes"] == 600


def test_list_subjects(client, auth_headers, subject):
    response = client.get("/subjects", headers=auth_headers)

    assert response.status_code == 200
    data = response.json()
    assert len(data) == 1
    assert data[0]["name"] == "Software and Web Technology"


def test_get_subject(client, auth_headers, subject):
    response = client.get(f"/subjects/{subject.id}", headers=auth_headers)

    assert response.status_code == 200
    data = response.json()
    assert data["id"] == subject.id
    assert data["name"] == subject.name


def test_get_subject_not_found(client, auth_headers):
    response = client.get("/subjects/999", headers=auth_headers)

    assert response.status_code == 404
    assert response.json()["detail"] == "Subject not found"


def test_update_subject(client, auth_headers, subject):
    response = client.put(
        f"/subjects/{subject.id}",
        headers=auth_headers,
        json={
            "name": "SWT Updated",
            "target_weekly_minutes": 720,
        },
    )

    assert response.status_code == 200
    data = response.json()
    assert data["name"] == "SWT Updated"
    assert data["target_weekly_minutes"] == 720


def test_delete_subject(client, auth_headers, subject):
    response = client.delete(f"/subjects/{subject.id}", headers=auth_headers)

    assert response.status_code == 200
    assert response.json()["detail"] == "Subject deleted"