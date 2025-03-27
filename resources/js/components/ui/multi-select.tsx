import * as React from "react"
import { Check, ChevronsUpDown, X } from "lucide-react"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from "@/components/ui/command"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

export interface Option {
  value: string
  label: string
}

interface MultiSelectProps {
  options: Option[]
  value: string[]
  onChange: (value: string[]) => void
  placeholder?: string
}

export function MultiSelect({
  options,
  value,
  onChange,
  placeholder = "Select items...",
}: MultiSelectProps) {
  const [searchTerm, setSearchTerm] = React.useState('')
  const [newTag, setNewTag] = React.useState('')

  const filteredOptions = options.filter(option =>
    option.label.toLowerCase().includes(searchTerm.toLowerCase())
  )

  const handleSelect = (optionValue: string) => {
    const newValue = value.includes(optionValue)
      ? value.filter(v => v !== optionValue)
      : [...value, optionValue]
    onChange(newValue)
  }

  const handleAddNewTag = () => {
    if (newTag && !options.find(opt => opt.label.toLowerCase() === newTag.toLowerCase())) {
      const newOption = { value: newTag.toLowerCase(), label: newTag }
      options.push(newOption)
      handleSelect(newOption.value)
      setNewTag('')
    }
  }

  return (
    <div className="space-y-2">
      <div className="flex items-center gap-2">
        <input
          type="text"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          placeholder={`Search ${placeholder.toLowerCase()}...`}
          className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
        />
      </div>
      
      <div className="max-h-[200px] overflow-y-auto rounded-md border p-2">
        {filteredOptions.length === 0 ? (
          <div className="py-6 text-center text-sm text-muted-foreground">
            No items found.
          </div>
        ) : (
          <div className="space-y-1">
            {filteredOptions.map(option => (
              <label
                key={option.value}
                className="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent cursor-pointer"
              >
                <input
                  type="checkbox"
                  checked={value.includes(option.value)}
                  onChange={() => handleSelect(option.value)}
                  className="h-4 w-4 rounded border-gray-300"
                />
                {option.label}
              </label>
            ))}
          </div>
        )}
      </div>

      {placeholder.toLowerCase().includes('tag') && (
        <div className="flex items-center gap-2">
          <input
            type="text"
            value={newTag}
            onChange={(e) => setNewTag(e.target.value)}
            placeholder="Add new tag..."
            className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            onKeyPress={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault()
                handleAddNewTag()
              }
            }}
          />
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={handleAddNewTag}
            disabled={!newTag}
          >
            Add
          </Button>
        </div>
      )}

      {value.length > 0 && (
        <div className="flex flex-wrap gap-1 pt-2">
          {value.map((v, i) => {
            const label = options.find(opt => opt.value === v)?.label
            if (!label) return null
            return (
              <div
                key={i}
                className="flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-sm dark:bg-slate-800"
              >
                {label}
                <button
                  type="button"
                  className="ml-1 rounded-full outline-none ring-offset-2 focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                  onClick={() => handleSelect(v)}
                >
                  <X className="h-3 w-3" />
                </button>
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}