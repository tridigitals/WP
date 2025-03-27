import React, { ChangeEvent, useState } from 'react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-react';

export type Column<T> = {
  key: keyof T | 'actions' | 'select';
  label: string;
  sortable?: boolean;
  render?: (item: T) => React.ReactNode;
};

export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface DataTableFilters {
  search?: string;
  sort?: string;
  direction?: 'asc' | 'desc';
  per_page?: number;
}

interface DataTableProps<T> {
  data: PaginatedData<T>;
  columns: ReadonlyArray<Column<T>>;
  filters: DataTableFilters;
  onSearch?: (value: string) => void;
  tableClasses?: string;
  onBulkAction?: (action: string, selectedItems: T[]) => void;
}

export function DataTableEnhanced<T>({
  data,
  columns,
  filters,
  onSearch,
  tableClasses,
  onBulkAction,
}: DataTableProps<T>) {
  const [selectedItems, setSelectedItems] = useState<T[]>([]);

  const handleSort = (key: string) => {
    const direction = filters.sort === key && filters.direction === 'asc' ? 'desc' : 'asc';
    router.get(window.location.pathname, { ...filters, sort: key, direction }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handlePerPageChange = (e: ChangeEvent<HTMLSelectElement>) => {
    router.get(window.location.pathname, { ...filters, per_page: e.target.value }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  // Debounced search with timeout
  let searchTimeout: NodeJS.Timeout;
  const handleSearch = (value: string) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      router.get(window.location.pathname, { ...filters, search: value }, {
        preserveState: true,
        preserveScroll: true,
      });
    }, 300);
  };

  const getSortIcon = (key: string) => {
    if (filters.sort !== key) {
      return <ChevronsUpDown className="h-4 w-4 text-gray-400" />;
    }
    return filters.direction === 'asc' ? <ChevronUp className="h-4 w-4 text-primary" /> : <ChevronDown className="h-4 w-4 text-primary" />;
  };

  const handleSelectItem = (item: T) => {
    setSelectedItems((prevSelected) => {
      const isSelected = prevSelected.some((selectedItem) => selectedItem === item);
      return isSelected ? prevSelected.filter((selectedItem) => selectedItem !== item) : [...prevSelected, item];
    });
  };

  const handleSelectAll = () => {
    setSelectedItems(data.data);
  };

  const handleBulkAction = (action: string) => {
    if (onBulkAction) {
      onBulkAction(action, selectedItems);
    }
  };

  return (
    <div className={`space-y-4 ${tableClasses}`}>
      <div className="flex justify-between items-center">
        <div className="flex items-center gap-4">
          <input
            type="text"
            placeholder="Search..."
            className="h-9 w-[150px] lg:w-[250px] rounded-md border-gray-300 px-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
            defaultValue={filters.search}
            onChange={(e) => handleSearch(e.target.value)}
          />
          <select
            className="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
            value={filters.per_page}
            onChange={handlePerPageChange}
          >
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
          </select>
        </div>
        <div className="text-sm text-gray-500 dark:text-gray-400">
          Total: {data.total} records
        </div>
      </div>

      <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
        <div className="overflow-x-auto">
          <Table className="table-auto">
            <TableHeader>
              <TableRow>
                <TableHead>
                  <input type="checkbox" checked={selectedItems.length === data.data.length} onChange={handleSelectAll} />
                </TableHead>
                {columns.map((column) => (
                  <TableHead
                    key={String(column.key)}
                    className={column.sortable ? 'cursor-pointer select-none hover:bg-accent/50' : ''}
                    onClick={() => column.sortable && handleSort(String(column.key))}
                  >
                    <div className="flex items-center gap-2">
                      {column.label}
                      {column.sortable && <span className="inline-flex">{getSortIcon(String(column.key))}</span>}
                    </div>
                  </TableHead>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody>
              {data.data.map((item, index) => (
                <TableRow key={index}>
                  <TableCell>
                    <input type="checkbox" checked={selectedItems.some((selectedItem) => selectedItem === item)} onChange={() => handleSelectItem(item)} />
                  </TableCell>
                  {columns.map((column) => (
                    <TableCell key={String(column.key)}>
                      {column.render ? column.render(item) : column.key === 'actions' ? null : String(item[column.key as keyof T] ?? '')}
                    </TableCell>
                  ))}
                </TableRow>
              ))}
              {data.data.length === 0 && (
                <TableRow>
                  <TableCell colSpan={columns.length + 1} className="h-24 text-center text-gray-500 dark:text-gray-400">
                    No records found
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </div>
      </div>

      <div className="flex items-center justify-between">
        <div className="text-sm text-gray-500 dark:text-gray-400">
          Showing {((data.current_page - 1) * data.per_page) + 1} to {Math.min(data.current_page * data.per_page, data.total)} of {data.total} results
        </div>
        <div className="flex gap-2">
          {data.current_page > 1 && (
            <Button
              variant="outline"
              size="sm"
              onClick={() =>
                router.get(window.location.pathname, { ...filters, page: data.current_page - 1 }, {
                  preserveState: true,
                  preserveScroll: true,
                })
              }
            >
              Previous
            </Button>
          )}
          {data.current_page < data.last_page && (
            <Button
              variant="outline"
              size="sm"
              onClick={() =>
                router.get(window.location.pathname, { ...filters, page: data.current_page + 1 }, {
                  preserveState: true,
                  preserveScroll: true,
                })
              }
            >
              Next
            </Button>
          )}
        </div>
      </div>
      {selectedItems.length > 0 && (
        <div className="flex gap-2">
          <Button onClick={() => handleBulkAction('delete')}>Delete Selected</Button>
          <Button onClick={() => handleBulkAction('edit')}>Edit Selected</Button>
        </div>
      )}
    </div>
  );
}