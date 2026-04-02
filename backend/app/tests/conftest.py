from collections.abc import Generator

import pytest
from fastapi.testclient import TestClient
from sqlalchemy import create_engine
from sqlalchemy.orm import Session, sessionmaker
from sqlalchemy.pool import StaticPool

from app.api.deps import get_db
from app.core.security import create_access_token, hash_password
from app.main import app

from app.core.database import Base
from app.models import Subject, Task, User


SQLALCHEMY_TEST_DATABASE_URL = "sqlite://"

engine = create_engine(
    SQLALCHEMY_TEST_DATABASE_URL,
    connect_args={"check_same_thread": False},
    poolclass=StaticPool,
)

TestingSessionLocal = sessionmaker(
    autocommit=False,
    autoflush=False,
    bind=engine,
)


@pytest.fixture(scope="function")
def db_session() -> Generator[Session, None, None]:
    Base.metadata.drop_all(bind=engine)
    Base.metadata.create_all(bind=engine)

    db = TestingSessionLocal()
    try:
        yield db
    finally:
        db.close()


@pytest.fixture(scope="function")
def client(db_session: Session) -> Generator[TestClient, None, None]:
    def override_get_db():
        try:
            yield db_session
        finally:
            pass

    app.dependency_overrides[get_db] = override_get_db

    with TestClient(app) as test_client:
        yield test_client

    app.dependency_overrides.clear()


@pytest.fixture(scope="function")
def test_user(db_session: Session) -> User:
    user = User(
        username="demo",
        hashed_password=hash_password("demo12345"),
    )
    db_session.add(user)
    db_session.commit()
    db_session.refresh(user)
    return user


@pytest.fixture(scope="function")
def second_user(db_session: Session) -> User:
    user = User(
        username="otheruser",
        hashed_password=hash_password("other12345"),
    )
    db_session.add(user)
    db_session.commit()
    db_session.refresh(user)
    return user


@pytest.fixture(scope="function")
def access_token(test_user: User) -> str:
    return create_access_token(subject=str(test_user.id))


@pytest.fixture(scope="function")
def auth_headers(access_token: str) -> dict[str, str]:
    return {"Authorization": f"Bearer {access_token}"}


@pytest.fixture(scope="function")
def subject(db_session: Session, test_user: User) -> Subject:
    subject = Subject(
        user_id=test_user.id,
        code="SWT",
        name="Software and Web Technology",
        description="Course project subject",
        target_total_minutes=3000,
        target_weekly_minutes=600,
    )
    db_session.add(subject)
    db_session.commit()
    db_session.refresh(subject)
    return subject


@pytest.fixture(scope="function")
def task(db_session: Session, test_user: User, subject: Subject) -> Task:
    task = Task(
        user_id=test_user.id,
        subject_id=subject.id,
        title="Implement auth API",
        description="Create register and login endpoints",
        status="todo",
        priority="high",
        estimated_minutes=120,
    )
    db_session.add(task)
    db_session.commit()
    db_session.refresh(task)
    return task