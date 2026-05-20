export const NEW_SUBJECT_VALUE = "__custom_subject__";

export async function fetchSubjectOptions() {
  const response = await fetch("/api/subjects", {
    credentials: "include",
  });
  const subjects = await response.json();

  if (!response.ok) {
    throw new Error(subjects?.error || "Failed to fetch subjects.");
  }

  if (!Array.isArray(subjects)) {
    return [];
  }

  return subjects
    .map((subject) => {
      const id = Number(subject?.id);
      const name = subject?.name?.trim();

      if (!Number.isFinite(id) || !name) {
        return null;
      }

      return { id, name };
    })
    .filter(Boolean);
}

export async function deleteSubjectOption(id) {
  const response = await fetch(`/api/subjects/${id}/delete`, {
    method: "POST",
    credentials: "include",
  });

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result?.error || "Failed to delete subject.");
  }

  return result;
}
