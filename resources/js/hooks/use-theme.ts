import { create } from 'zustand';
import { persist, PersistOptions } from 'zustand/middleware';

type Theme = 'light' | 'dark';

interface ThemeStore {
  theme: Theme;
  setTheme: (theme: Theme) => void;
  toggleTheme: () => void;
}

type ThemeStoreWithPersist = ThemeStore & {
  _hasHydrated?: boolean;
};

const persistConfig: PersistOptions<ThemeStoreWithPersist> = {
  name: 'theme-storage',
  onRehydrateStorage: () => (state) => {
    if (state) state._hasHydrated = true;
  },
};

export const useTheme = create<ThemeStoreWithPersist>()(
  persist(
    (set) => ({
      theme: 'light',
      _hasHydrated: false,
      setTheme: (theme: Theme) => set({ theme }),
      toggleTheme: () => set((state) => ({ theme: state.theme === 'light' ? 'dark' : 'light' })),
    }),
    persistConfig
  )
);