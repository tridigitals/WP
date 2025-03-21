import * as React from "react";
import { Loader2, Plus, Tag as TagIcon } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/components/ui/command";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { Badge } from "@/components/ui/badge";
import { router } from "@inertiajs/react";
import { Tag } from "@/types";

interface TagInputProps {
  selectedTags: number[];
  onTagsChange: (tagIds: number[]) => void;
}

interface CommandProps {
  children: React.ReactNode;
  className?: string;
}

interface CommandItemProps extends CommandProps {
  value: string;
  onSelect: () => void;
}

export function TagInput({ selectedTags, onTagsChange }: TagInputProps) {
  const [open, setOpen] = React.useState(false);
  const [query, setQuery] = React.useState("");
  const [tags, setTags] = React.useState<Tag[]>([]);
  const [isLoading, setIsLoading] = React.useState(false);

  const fetchTags = React.useCallback(async (search: string) => {
    setIsLoading(true);
    try {
      const response = await fetch(
        route('admin.tags.suggestions', { q: search })
      );
      const data = await response.json();
      setTags(data);
    } catch (error) {
      console.error('Error fetching tags:', error);
    }
    setIsLoading(false);
  }, []);

  React.useEffect(() => {
    fetchTags(query);
  }, [query, fetchTags]);

  const handleCreateTag = () => {
    router.get(route('admin.tags.create'));
  };

  const selectedTagItems = tags.filter(tag => selectedTags.includes(tag.id));

  return (
    <div className="flex flex-col gap-2">
      <Popover open={open} onOpenChange={setOpen}>
        <PopoverTrigger asChild>
          <Button
            variant="outline"
            role="combobox"
            aria-expanded={open}
            className="w-full justify-between text-left font-normal"
          >
            <div className="flex flex-wrap gap-1">
              {selectedTagItems.length > 0 ? (
                selectedTagItems.map(tag => (
                  <Badge 
                    key={tag.id}
                    variant="secondary"
                    className="mr-1"
                  >
                    {tag.name}
                  </Badge>
                ))
              ) : (
                <span className="text-muted-foreground">
                  Pilih atau cari tag...
                </span>
              )}
            </div>
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-[400px] p-0" align="start">
          <Command className="w-full" shouldFilter={false}>
            <CommandInput 
              placeholder="Cari tag..."
              value={query}
              onValueChange={setQuery}
              className="border-none focus:ring-0"
            />
            <CommandList>
              <CommandEmpty className="p-4">
                <div className="flex flex-col items-center justify-center gap-2">
                  <p className="text-sm text-muted-foreground">
                    Tag tidak ditemukan
                  </p>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={handleCreateTag}
                    className="w-full"
                  >
                    <Plus className="mr-2 h-4 w-4" />
                    Buat tag baru
                  </Button>
                </div>
              </CommandEmpty>
              {isLoading ? (
                <div className="flex items-center justify-center p-4">
                  <Loader2 className="h-4 w-4 animate-spin" />
                </div>
              ) : (
                <CommandGroup heading="Tags">
                  {tags.map(tag => {
                    const isSelected = selectedTags.includes(tag.id);
                    return (
                      <CommandItem
                        key={tag.id}
                        onSelect={() => {
                          const newSelection = isSelected
                            ? selectedTags.filter(id => id !== tag.id)
                            : [...selectedTags, tag.id];
                          onTagsChange(newSelection);
                        }}
                      >
                        <div
                          className={cn(
                            "mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary",
                            isSelected
                              ? "bg-primary text-primary-foreground"
                              : "opacity-50 [&_svg]:invisible"
                          )}
                        >
                          <TagIcon className="h-3 w-3" />
                        </div>
                        {tag.name}
                      </CommandItem>
                    );
                  })}
                </CommandGroup>
              )}
            </CommandList>
          </Command>
        </PopoverContent>
      </Popover>
    </div>
  );
}