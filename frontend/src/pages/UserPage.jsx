import React, { useEffect, useState } from "react";
import { Camera, KeyRound, Save, Trash2, UserRound } from "lucide-react";
import { useAuth } from "@/lib/auth";
import PageBackdrop from "@/components/shared/PageBackdrop";

function initialsFromUsername(username) {
  const safe = (username || "U").trim();
  return safe.slice(0, 2).toUpperCase();
}


export default function UserPage() {
  const auth = useAuth();
  const [username, setUsername] = useState("");
  const [profileMessage, setProfileMessage] = useState("");
  const [passwordMessage, setPasswordMessage] = useState("");
  const [isSavingProfile, setIsSavingProfile] = useState(false);
  const [isSavingPassword, setIsSavingPassword] = useState(false);
  const [isSavingProfileImage, setIsSavingProfileImage] = useState(false);
  const [passwords, setPasswords] = useState({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  });

  useEffect(() => {
    setUsername(auth.user?.username || "");
  }, [auth.user]);

  const handleProfileSubmit = async (event) => {
    event.preventDefault();
    setIsSavingProfile(true);
    setProfileMessage("");

    try {
      const response = await fetch("/api/user/profile", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username }),
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data?.error || "Failed to update profile.");
      }

      await auth.getUser();
      setProfileMessage("Profile updated.");
    } catch (error) {
      setProfileMessage(String(error?.message || error));
    } finally {
      setIsSavingProfile(false);
    }
  };

  const handlePasswordSubmit = async (event) => {
    event.preventDefault();
    setIsSavingPassword(true);
    setPasswordMessage("");

    try {
      const response = await fetch("/api/user/password", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(passwords),
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data?.error || "Failed to update password.");
      }

      setPasswords({
        currentPassword: "",
        newPassword: "",
        confirmPassword: "",
      });
      setPasswordMessage("Password updated.");
    } catch (error) {
      setPasswordMessage(String(error?.message || error));
    } finally {
      setIsSavingPassword(false);
    }
  };

  const handleProfileImageChange = (event) => {
    const file = event.target.files?.[0];

    if (!file) {
      return;
    }

    if (!file.type.startsWith("image/")) {
      setProfileMessage("Please choose an image file.");
      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      setProfileMessage("Profile picture must be 2 MB or smaller.");
      return;
    }

    const formData = new FormData();
    formData.append("profileImage", file);

    setIsSavingProfileImage(true);
    setProfileMessage("");

    fetch("/api/user/profile-image", {
      method: "POST",
      credentials: "include",
      body: formData,
    })
      .then(async (response) => {
        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.error || "Failed to upload profile picture.");
        }

        await auth.getUser();
        setProfileMessage("Profile picture updated.");
      })
      .catch((error) => {
        setProfileMessage(String(error?.message || error));
      })
      .finally(() => {
        setIsSavingProfileImage(false);
        event.target.value = "";
      });
  };

  const clearProfileImage = () => {
    setIsSavingProfileImage(true);
    setProfileMessage("");

    fetch("/api/user/profile-image/delete", {
      method: "GET",
      credentials: "include",
    })
      .then(async (response) => {
        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.error || "Failed to remove profile picture.");
        }

        await auth.getUser();
        setProfileMessage("Profile picture removed.");
      })
      .catch((error) => {
        setProfileMessage(String(error?.message || error));
      })
      .finally(() => {
        setIsSavingProfileImage(false);
      });
  };

  return (
    <div className="relative min-h-screen overflow-hidden bg-bg-base text-text-main">
      <PageBackdrop />

      <div className="relative mx-auto max-w-5xl px-4 pb-12 pt-22 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h1 className="text-3xl font-semibold">User Settings</h1>
          <p className="mt-2 text-sm text-text-secondary">
            Update your account details and personal profile image.
          </p>
        </div>

        <div className="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
          <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
            <div className="flex flex-col items-center text-center">
              <div className="relative">
                {auth.user?.profileImageUrl ? (
                  <img
                    src={auth.user.profileImageUrl}
                    alt="Profile"
                    className="h-30 w-30 rounded-3xl object-cover ring-4 ring-primary/10"
                  />
                ) : (
                  <div className="flex h-30 w-30 items-center justify-center rounded-3xl bg-gradient-primary-accent text-3xl font-semibold text-white ring-4 ring-primary/10">
                    {initialsFromUsername(auth.user?.username)}
                  </div>
                )}
                <div className="absolute -bottom-2 -right-2 flex h-10 w-10 items-center justify-center rounded-2xl border border-border-base bg-bg-card text-primary shadow-sm">
                  <Camera className="h-4 w-4" />
                </div>
              </div>

              <h2 className="mt-5 text-xl font-semibold">{auth.user?.username || "User"}</h2>
              <p className="mt-1 text-sm text-text-secondary">
                Upload an image and it will be stored on the server.
              </p>
            </div>

            <div className="mt-6 space-y-3">
              <label className="flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 font-medium text-white transition hover:bg-primary-dark">
                <Camera className="h-4 w-4" />
                {isSavingProfileImage ? "Uploading..." : "Upload Picture"}
                <input
                  type="file"
                  accept="image/*"
                  className="hidden"
                  onChange={handleProfileImageChange}
                  disabled={isSavingProfileImage}
                />
              </label>

              <button
                type="button"
                onClick={clearProfileImage}
                disabled={isSavingProfileImage}
                className="flex w-full items-center justify-center gap-2 rounded-2xl border border-danger/20 bg-danger/10 px-4 py-3 font-medium text-danger transition hover:bg-danger/15"
              >
                <Trash2 className="h-4 w-4" />
                Remove Picture
              </button>
            </div>
          </section>

          <div className="space-y-6">
            <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
              <div className="flex items-start gap-4">
                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                  <UserRound className="h-5 w-5" />
                </div>
                <div>
                  <h2 className="text-lg font-semibold">Profile</h2>
                  <p className="mt-1 text-sm text-text-secondary">
                    Change the username tied to your account.
                  </p>
                </div>
              </div>

              <form onSubmit={handleProfileSubmit} className="mt-6 space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    Username
                  </label>
                  <input
                    type="text"
                    value={username}
                    onChange={(event) => setUsername(event.target.value)}
                    className="w-full rounded-2xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    required
                  />
                </div>

                {profileMessage ? (
                  <p className="text-sm text-text-secondary">{profileMessage}</p>
                ) : null}

                <button
                  type="submit"
                  disabled={isSavingProfile}
                  className="inline-flex items-center gap-2 rounded-2xl bg-primary px-5 py-3 font-medium text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <Save className="h-4 w-4" />
                  {isSavingProfile ? "Saving..." : "Save Profile"}
                </button>
              </form>
            </section>

            <section className="rounded-3xl border border-border-base bg-bg-card p-6 shadow-sm">
              <div className="flex items-start gap-4">
                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent/10 text-accent">
                  <KeyRound className="h-5 w-5" />
                </div>
                <div>
                  <h2 className="text-lg font-semibold">Password</h2>
                  <p className="mt-1 text-sm text-text-secondary">
                    Use your current password to set a new one.
                  </p>
                </div>
              </div>

              <form onSubmit={handlePasswordSubmit} className="mt-6 space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    Current Password
                  </label>
                  <input
                    type="password"
                    value={passwords.currentPassword}
                    onChange={(event) => setPasswords((current) => ({
                      ...current,
                      currentPassword: event.target.value,
                    }))}
                    className="w-full rounded-2xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    required
                  />
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    New Password
                  </label>
                  <input
                    type="password"
                    value={passwords.newPassword}
                    onChange={(event) => setPasswords((current) => ({
                      ...current,
                      newPassword: event.target.value,
                    }))}
                    className="w-full rounded-2xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    required
                  />
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-text-secondary">
                    Confirm New Password
                  </label>
                  <input
                    type="password"
                    value={passwords.confirmPassword}
                    onChange={(event) => setPasswords((current) => ({
                      ...current,
                      confirmPassword: event.target.value,
                    }))}
                    className="w-full rounded-2xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                    required
                  />
                </div>

                {passwordMessage ? (
                  <p className="text-sm text-text-secondary">{passwordMessage}</p>
                ) : null}

                <button
                  type="submit"
                  disabled={isSavingPassword}
                  className="inline-flex items-center gap-2 rounded-2xl bg-accent px-5 py-3 font-medium text-white transition hover:bg-accent-dark disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <KeyRound className="h-4 w-4" />
                  {isSavingPassword ? "Updating..." : "Update Password"}
                </button>
              </form>
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}
