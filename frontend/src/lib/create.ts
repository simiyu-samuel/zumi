export type UserRole = "user" | "pro" | "studio" | "admin";

export type CreateType = "wave" | "skill-drop" | "challenge" | "circle" | "room";

export interface CreateOption {
  id: CreateType;
  title: string;
  shortTitle: string;
  description: string;
  href: string;
  minRole: UserRole;
  accent: string;
  eyebrow: string;
}

const ROLE_ORDER: UserRole[] = ["user", "pro", "studio", "admin"];

export const CREATE_OPTIONS: CreateOption[] = [
  {
    id: "wave",
    title: "Create a Wave",
    shortTitle: "Wave",
    description: "Upload a short-form video and publish it into the Waves and discovery feeds.",
    href: "/create/wave",
    minRole: "user",
    accent: "teal",
    eyebrow: "Short video",
  },
  {
    id: "circle",
    title: "Start a Circle",
    shortTitle: "Circle",
    description: "Open a creator-owned community for members, events, exclusives, and deeper connection.",
    href: "/create/circle",
    minRole: "user",
    accent: "emerald",
    eyebrow: "Community",
  },
  {
    id: "skill-drop",
    title: "Publish a Skill Drop",
    shortTitle: "Skill Drop",
    description: "Package knowledge into a paid digital product with preview and content delivery links.",
    href: "/create/skill-drop",
    minRole: "pro",
    accent: "violet",
    eyebrow: "Monetized knowledge",
  },
  {
    id: "room",
    title: "Host a Gated Room",
    shortTitle: "Room",
    description: "Launch a live or scheduled room with an entry fee and direct creator earnings.",
    href: "/create/room",
    minRole: "pro",
    accent: "rose",
    eyebrow: "Live experience",
  },
  {
    id: "challenge",
    title: "Launch a Wave Challenge",
    shortTitle: "Challenge",
    description: "Create a prize-backed challenge and invite the community to submit response waves.",
    href: "/create/challenge",
    minRole: "pro",
    accent: "amber",
    eyebrow: "Community activation",
  },
];

export function normalizeRole(role?: string | null): UserRole {
  if (role === "admin" || role === "studio" || role === "pro") {
    return role;
  }

  return "user";
}

export function canAccessRole(currentRole: string | null | undefined, minRole: UserRole): boolean {
  return ROLE_ORDER.indexOf(normalizeRole(currentRole)) >= ROLE_ORDER.indexOf(minRole);
}

export function getCreateOption(type: string): CreateOption | undefined {
  return CREATE_OPTIONS.find((option) => option.id === type);
}

export function getAccessibleCreateOptions(role?: string | null): CreateOption[] {
  return CREATE_OPTIONS.filter((option) => canAccessRole(role, option.minRole));
}

export function getLockedCreateOptions(role?: string | null): CreateOption[] {
  return CREATE_OPTIONS.filter((option) => !canAccessRole(role, option.minRole));
}

export function getRoleLabel(role?: string | null): string {
  const normalized = normalizeRole(role);
  return normalized === "user" ? "Free" : normalized.charAt(0).toUpperCase() + normalized.slice(1);
}
