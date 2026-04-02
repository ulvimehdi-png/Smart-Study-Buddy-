def test_register(client):
    response = client.post(
        "/auth/register",
        json={
            "username": "newuser",
            "password": "strongpass123",
        },
    )

    assert response.status_code == 201
    data = response.json()
    assert data["username"] == "newuser"
    assert "id" in data
    assert "created_at" in data


def test_register_duplicate_username(client, test_user):
    response = client.post(
        "/auth/register",
        json={
            "username": "demo",
            "password": "anotherpass123",
        },
    )

    assert response.status_code == 400
    assert response.json()["detail"] == "Username already exists"


def test_login_success(client, test_user):
    response = client.post(
        "/auth/login",
        data={
            "username": "demo",
            "password": "demo12345",
        },
    )

    assert response.status_code == 200
    data = response.json()
    assert "access_token" in data
    assert data["token_type"] == "bearer"


def test_login_invalid_password(client, test_user):
    response = client.post(
        "/auth/login",
        data={
            "username": "demo",
            "password": "wrongpassword",
        },
    )

    assert response.status_code == 401
    assert response.json()["detail"] == "Invalid username or password"


def test_me(client, auth_headers):
    response = client.get("/auth/me", headers=auth_headers)

    assert response.status_code == 200
    data = response.json()
    assert data["username"] == "demo"