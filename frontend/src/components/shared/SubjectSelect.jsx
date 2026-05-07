import React, { useEffect, useRef, useState } from "react";
import { Check, ChevronDown, PlusCircle, Trash2 } from "lucide-react";
import { cn } from "@/lib/utils";
import { NEW_SUBJECT_VALUE } from "@/lib/subjectOptions";

function SubjectOptionRow({
  label,
  isSelected,
  onClick,
  onRemove,
  icon = null,
  tone = "default",
}) {
  return (
    <div
      className={cn(
        "flex items-center gap-2 rounded-2xl px-3 py-2 transition group",
        tone === "create" ? "bg-accent/5" : "hover:bg-primary/8",
      )}
    >
      <button
        type="button"
        onClick={onClick}
        className={cn(
          "flex min-w-0 flex-1 items-center gap-3 text-left h-9 w-9",
          tone === "create" ? "text-accent" : "text-text-main",
        )}
      >
        {icon ? (
          <span
            className={cn(
              "flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border",
              tone === "create"
                ? "border-accent/20 bg-accent/10 text-accent"
                : "border-border-base bg-bg-base text-text-secondary",
            )}
          >
            {icon}
          </span>
        ) : null}
        {isSelected && (
        <Check className="h-4 w-4 text-primary" />
        )}
        <span className="min-w-0 flex-1 truncate text-sm font-medium">{label}</span>
      </button>

      {onRemove ? (
        <button
          type="button"
          onClick={onRemove}
          className={cn("flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-danger/15 bg-danger/8 text-danger transition hover:bg-danger/14",
                        isSelected ? "opacity-100" : "opacity-0 group-hover:opacity-100"
          )}
          aria-label={`Remove ${label}`}
        >
          <Trash2 className="h-4 w-4" />
        </button>
      ) : null}
    </div>
  );
}

function SubjectModal({
  icon,
  title,
  description,
  children,
  onCancel,
  onConfirm,
  confirmLabel,
  confirmDisabled = false,
  confirmTone = "primary",
}) {
  return (
    <div className="fixed inset-0 z-40 flex items-center justify-center bg-black/45 px-4">
      <div className="w-full max-w-lg rounded-3xl border border-border-base bg-bg-card p-5 shadow-2xl">
        <div className="flex items-start gap-4">
          <div
            className={cn(
              "flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl",
              confirmTone === "danger"
                ? "bg-danger/10 text-danger"
                : "bg-accent/10 text-accent",
            )}
          >
            {icon}
          </div>
          <div className="min-w-0 flex-1">
            <h3 className="text-lg font-semibold text-text-main">{title}</h3>
            <p className="mt-1 text-sm text-text-secondary">{description}</p>
          </div>
        </div>

        <div className="mt-5">{children}</div>

        <div className="mt-5 flex justify-end gap-3">
          <button
            type="button"
            onClick={onCancel}
            className="rounded-xl border border-border-base bg-bg-elevated px-4 py-2.5 text-sm font-medium text-text-secondary transition hover:bg-bg-base"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={onConfirm}
            disabled={confirmDisabled}
            className={cn(
              "rounded-xl px-4 py-2.5 text-sm font-medium text-white transition disabled:cursor-not-allowed disabled:opacity-50",
              confirmTone === "danger"
                ? "bg-danger hover:bg-danger/90"
                : "bg-primary hover:bg-primary-dark",
            )}
          >
            {confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
}

export default function SubjectSelect({
  options,
  value,
  onChange,
  customSubject,
  onCustomSubjectChange,
  onRemoveOption,
  placeholder = "Choose a subject",
  emptyLabel = "No subject",
  customLabel = "Create new subject",
  className,
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [draftSubject, setDraftSubject] = useState("");
  const [optionToDelete, setOptionToDelete] = useState(null);
  const [isRemoving, setIsRemoving] = useState(false);
  const rootRef = useRef(null);

  useEffect(() => {
    function handleEscape(event) {
      if (event.key === "Escape") {
        setIsOpen(false);
        setIsCreateModalOpen(false);
        setIsDeleteModalOpen(false);
      }
    }

    function handlePointerDown(event) {
      if (!rootRef.current?.contains(event.target)) {
        setIsOpen(false);
      }
    }

    document.addEventListener("keydown", handleEscape);
    document.addEventListener("mousedown", handlePointerDown);

    return () => {
      document.removeEventListener("keydown", handleEscape);
      document.removeEventListener("mousedown", handlePointerDown);
    };
  }, []);

  const selectedLabel =
    value === NEW_SUBJECT_VALUE
      ? customSubject?.trim() || customLabel
      : value || emptyLabel;

  const hasSelection = value === NEW_SUBJECT_VALUE || Boolean(value);

  const handleSelect = (nextValue) => {
    onChange(nextValue);
    setIsOpen(false);
  };

  const openCreateModal = () => {
    setDraftSubject(customSubject);
    setIsOpen(false);
    setIsCreateModalOpen(true);
  };

  const closeCreateModal = () => {
    setIsCreateModalOpen(false);
    setDraftSubject("");
  };

  const confirmCreateSubject = () => {
    const nextSubject = draftSubject.trim();

    if (!nextSubject) {
      return;
    }

    onCustomSubjectChange(nextSubject);
    onChange(NEW_SUBJECT_VALUE);
    closeCreateModal();
  };

  const askRemoveOption = (option) => {
    setOptionToDelete(option);
    setIsOpen(false);
    setIsDeleteModalOpen(true);
  };

  const closeDeleteModal = () => {
    setIsDeleteModalOpen(false);
    setOptionToDelete(null);
    setIsRemoving(false);
  };

  const confirmDeleteOption = async () => {
    if (!optionToDelete || !onRemoveOption) {
      return;
    }

    setIsRemoving(true);

    try {
      await onRemoveOption(optionToDelete);

      if (value === optionToDelete.name) {
        onChange("");
      }

      closeDeleteModal();
    } catch (error) {
      console.error("Error removing subject:", error);
      setIsRemoving(false);
    }
  };

  return (
    <>
      <div ref={rootRef} className={cn("relative", className)}>
        <button
          type="button"
          onClick={() => setIsOpen((current) => !current)}
          className={cn(
            "flex w-full items-center gap-3 rounded-2xl border p-2 text-left shadow-sm transition",
            isOpen
              ? "border-primary bg-bg-card ring-2 ring-primary/10"
              : "border-border-base bg-bg-elevated hover:border-primary/40",
          )}
          aria-expanded={isOpen}
          aria-haspopup="listbox"
        >
          <span
            className={cn(
              "flex h-8 w-8 shrink-0 items-center justify-center rounded-md border",
              hasSelection
                ? "border-primary/20 bg-primary/10 text-primary"
                : "border-border-base bg-bg-base text-text-muted",
            )}
          >
            {value === NEW_SUBJECT_VALUE ? (
              <PlusCircle className="h-4 w-4" />
            ) : (
              <span className="text-xs font-semibold uppercase">
                {selectedLabel.slice(0, 2) || "--"}
              </span>
            )}
          </span>
          <span className="min-w-0 flex-1">
            <span
              className={cn(
                "block truncate text-sm font-medium",
                hasSelection ? "text-text-main" : "text-text-muted",
              )}
            >
              {hasSelection ? selectedLabel : placeholder}
            </span>
          </span>
          <ChevronDown
            className={cn(
              "h-4 w-4 shrink-0 text-text-secondary transition-transform",
              isOpen ? "rotate-180" : "",
            )}
          />
        </button>

        {isOpen ? (
          <div className="absolute left-0 right-0 z-30 mt-3 max-w-100 lg:w-100 rounded-3xl border border-border-base bg-bg-card p-2 shadow-xl">
            <div className="max-h-60 overflow-y-scroll pr-2 custom-scrollbar" role="listbox">
              <div className="space-y-1.5">
                <SubjectOptionRow
                  icon={<PlusCircle className="h-4 w-4" />}
                  label={customLabel}
                  isSelected={value === NEW_SUBJECT_VALUE}
                  onClick={openCreateModal}
                  tone="create"
                />
                
                <div className="my-2 border-t border-border-base" />

                <SubjectOptionRow
                  label={emptyLabel}
                  isSelected={!value}
                  onClick={() => handleSelect("")}
                />

                {options.map((option) => (
                  <SubjectOptionRow
                    key={option.id}
                    label={option.name}
                    isSelected={value === option.name}
                    onClick={() => handleSelect(option.name)}
                    onRemove={() => askRemoveOption(option)}
                  />
                ))}
              </div>
            </div>
          </div>
        ) : null}
      </div>

      {isCreateModalOpen ? (
        <SubjectModal
          icon={<PlusCircle className="h-5 w-5" />}
          title="Create New Subject"
          description="Enter the subject name you want to use for this item."
          onCancel={closeCreateModal}
          onConfirm={confirmCreateSubject}
          confirmLabel="Create Subject"
          confirmDisabled={!draftSubject.trim()}
        >
          <p className="mb-2 block text-xs font-semibold uppercase text-primary">
            Subject Name
          </p>
          <input
            type="text"
            value={draftSubject}
            onChange={(event) => setDraftSubject(event.target.value)}
            onKeyDown={(event) => {
              if (event.key === "Enter") {
                event.preventDefault();
                confirmCreateSubject();
              }
            }}
            placeholder="e.g. Math"
            className="w-full rounded-2xl border border-border-base bg-bg-elevated px-4 py-3 text-text-main placeholder:text-text-muted outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
            autoFocus
          />
        </SubjectModal>
      ) : null}

      {isDeleteModalOpen ? (
        <SubjectModal
          icon={<Trash2 className="h-5 w-5" />}
          title="Remove Subject"
          description={`Remove "${optionToDelete?.name || ""}" from your subjects? Existing notes and tasks will no longer show that subject.`}
          onCancel={closeDeleteModal}
          onConfirm={confirmDeleteOption}
          confirmLabel={isRemoving ? "Removing..." : "Remove Subject"}
          confirmDisabled={isRemoving}
          confirmTone="danger"
        >
          <div className="rounded-2xl border border-danger/15 bg-danger/8 px-4 py-3 text-sm text-text-secondary">
            This removes the subject from future selection lists and hides it from existing linked items.
          </div>
        </SubjectModal>
      ) : null}
    </>
  );
}
