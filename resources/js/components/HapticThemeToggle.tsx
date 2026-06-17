import React, { useEffect, useState } from "react";
import { Switch } from "@/components/ui/material-design-3-switch";
import { Moon, Sun } from "lucide-react";

export default function HapticThemeToggle() {
  const [isDark, setIsDark] = useState(false);

  useEffect(() => {
    // Initial sync with document state
    const currentTheme = document.documentElement.classList.contains("dark");
    setIsDark(currentTheme);
  }, []);

  const toggleTheme = (checked: boolean) => {
    setIsDark(checked);
    
    if (checked) {
      document.documentElement.classList.add("dark");
      localStorage.setItem("wp-theme", "dark");
    } else {
      document.documentElement.classList.remove("dark");
      localStorage.setItem("wp-theme", "light");
    }
  };

  return (
    <div className="flex items-center justify-center p-1 rounded-xl transition-all duration-300">
      <Switch 
        checked={isDark} 
        onCheckedChange={toggleTheme} 
        size="sm"
        haptic="heavy"
        checkedIcon={<Moon className="w-2.5 h-2.5 fill-current" />}
        uncheckedIcon={<Sun className="w-2.5 h-2.5 fill-current" />}
        className="peer-checked:bg-indigo-500 peer-checked:border-indigo-500 shadow-sm"
      />
    </div>
  );
}
