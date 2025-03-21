import { ReactNode } from "react";

export interface CommandProps {
  children?: ReactNode;
  className?: string;
  shouldFilter?: boolean;
}

export interface CommandInputProps {
  value?: string;
  placeholder?: string;
  onValueChange?: (value: string) => void;
  className?: string;
}

export interface CommandListProps {
  children?: ReactNode;
}

export interface CommandEmptyProps {
  children?: ReactNode;
  className?: string;
}

export interface CommandGroupProps {
  children?: ReactNode;
  heading?: string;
}

export interface CommandItemProps {
  children?: ReactNode;
  value?: string;
  onSelect?: () => void;
}