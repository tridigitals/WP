import { useTheme } from '@/components/theme-provider';
import type { Theme as Appearance } from '@/components/theme-provider';

// Re-export the type if needed elsewhere under the old name
export { type Appearance };

export function useAppearance() {
    // Use the theme context provided by ThemeProvider
    const { theme, setTheme } = useTheme();

    // Rename setTheme to updateAppearance for compatibility
    // with existing components using this hook.
    const updateAppearance = (mode: Appearance) => {
        setTheme(mode);
    };

    // Return the theme state and the setter function from the context
    return { appearance: theme, updateAppearance } as const;
}

// Removed prefersDark, setCookie, applyTheme, mediaQuery,
// handleSystemThemeChange, initializeTheme, local useState, and useEffect.
// Theme logic is now centralized in ThemeProvider.
