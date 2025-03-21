import * as React from "react"
import { Check, ChevronsUpDown, Plus } from "lucide-react"
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
import { Badge } from "@/components/ui/badge"
import { router } from "@inertiajs/react"

interface Tag {
  id: number
  name: string
}

interface Props {
  selectedTags: number[]
  onTagsChange: (tagIds: number[]) => void
}

export function TagSelect({ selectedTags, onTagsChange }: Props) {
  const [open, setOpen] = React.useState(false)
  const [tags, setTags] = React.useState<Tag[]>([])
  const [loading, setLoading] = React.useState(false)
  const [searchQuery, setSearchQuery] = React.useState("")

  const loadTags = async (query: string) => {
    setLoading(true)
    try {
      const response = await fetch(route('admin.tags.suggestions', { q: query }))
      const data = await response.json()
      setTags(data)
    } catch (error) {
      console.error('Error loading tags:', error)
    }
    setLoading(false)
  }

  React.useEffect(() => {
    loadTags(searchQuery)
  }, [searchQuery])

  const handleCreateTag = () => {
    router.get(route('admin.tags.create'))
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          className="w-full justify-between"
        >
          {selectedTags.length > 0 ? (
            <div className="flex flex-wrap gap-1">
              {selectedTags.map(id => {
                const tag = tags.find(t => t.id === id)
                return (
                  <Badge key={id} variant="secondary">
                    {tag?.name || 'Loading...'}
                  </Badge>
                )
              })}
            </div>
          ) : (
            "Select tags..."
          )}
          <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-full p-0">
        <Command>
          <CommandInput 
            placeholder="Search tags..." 
            value={searchQuery}
            onValueChange={setSearchQuery}
          />
          <CommandEmpty>
            <div className="flex flex-col items-center justify-center p-4">
              <p className="text-sm text-muted-foreground mb-2">No tags found</p>
              <Button
                variant="outline"
                size="sm"
                onClick={handleCreateTag}
                className="w-full"
              >
                <Plus className="mr-2 h-4 w-4" />
                Create new tag
              </Button>
            </div>
          </CommandEmpty>
          <CommandGroup>
            {tags.map((tag) => (
              <CommandItem
                key={tag.id}
                value={tag.name}
                onSelect={() => {
                  const isSelected = selectedTags.includes(tag.id)
                  const newSelection = isSelected
                    ? selectedTags.filter(id => id !== tag.id)
                    : [...selectedTags, tag.id]
                  onTagsChange(newSelection)
                }}
              >
                <Check
                  className={cn(
                    "mr-2 h-4 w-4",
                    selectedTags.includes(tag.id) ? "opacity-100" : "opacity-0"
                  )}
                />
                {tag.name}
              </CommandItem>
            ))}
          </CommandGroup>
        </Command>
      </PopoverContent>
    </Popover>
  )
}