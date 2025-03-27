import React, { ChangeEvent, useState } from 'react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export type Column<T extends { id: number }> = {
  key: keyof T | 'actions';
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

interface DataTableProps<T extends { id: number }> {
  data: PaginatedData<T>;
  columns: ReadonlyArray<Column<T>>;
  filters: DataTableFilters;
  className?: string;
  onBulkAction?: (action: string, selectedItems: T[]) => void;
}

export function EnhancedDataTable<T extends { id: number }>({
  data,
  columns,
  filters,
  className,
  onBulkAction,
}: DataTableProps<T>) {
  const [selectedItems, setSelectedItems] = useState<T[]>([]);
  const [bulkAction, setBulkAction] = useState('');

  const handleSort = (key: keyof T | 'actions') => {
    const direction = filters.sort === key && filters.direction === 'asc' ? 'desc' : 'asc';
    router.get(window.location.pathname, { ...filters, sort: String(key), direction }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handlePerPageChange = (e: ChangeEvent<HTMLSelectElement>) => {
    router.get(window.location.pathname, { ...filters, per_page: parseInt(e.target.value, 10) }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

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

  const getSortIcon = (key: keyof T | 'actions') => {
    if (key === 'actions') return null;
    if (filters.sort !== key) {
      return <ChevronsUpDown className="h-4 w-4 text-gray-400" />;
    }
    return filters.direction === 'asc'
      ? <ChevronUp className="h-4 w-4 text-primary" />
      : <ChevronDown className="h-4 w-4 text-primary" />;
  };

  const handleSelectItem = (item: T) => {
    setSelectedItems(prevSelected => {
      const isSelected = prevSelected.some(selectedItem => selectedItem.id === item.id);
      return isSelected
        ? prevSelected.filter(selectedItem => selectedItem.id !== item.id)
        : [...prevSelected, item];
    });
  };

  const handleBulkActionChange = (value: string) => {
    setBulkAction(value);
  };

  const handleApplyBulkAction = () => {
    if (bulkAction && selectedItems.length > 0) {
      onBulkAction!(bulkAction, selectedItems);
      setSelectedItems([]);
      setBulkAction('');
    }
  };

  return (
    <div className="space-y-4">
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
            value={filters.per_page || 10}
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

      <div className={`${className || ''} relative overflow-hidden rounded-xl border`}>
        <div className="overflow-x-auto">
          <Table>
            <TableHeader className="hidden md:table-header-group">
              <TableRow>
                <TableHead className="w-12 px-4"> {/* Checkbox column */}
                  <input
                    type="checkbox"
                    className="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                    checked={data.data.length > 0 && selectedItems.length === data.data.length}
                    onChange={(e) => {
                      setSelectedItems(e.target.checked ? data.data : []);
                    }}
                  />
                </TableHead>
                {columns.map((column) => (
                  <TableHead
                    key={column.key as string}
                    className={`px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 ${column.sortable ? 'cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-700' : ''}`}
                    onClick={() => column.sortable && handleSort(column.key)}
                  >
                    <div className="flex items-center gap-2">
                      {column.label}
                      {column.sortable && (
                        <span className="inline-flex">{getSortIcon(column.key)}</span>
                      )}
                    </div>
                  </TableHead>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
              {data.data.map((item, index) => (
                <TableRow key={index} className="block md:table-row border-b md:border-none mb-4 md:mb-0 bg-white dark:bg-gray-800 md:bg-transparent">
                  {/* Checkbox Cell - visible on all screens but styled differently */}
                  <TableCell className="px-4 py-2 md:w-12 md:px-6 md:py-4 whitespace-nowrap flex items-center md:table-cell border-b md:border-none">
                     <span className="md:hidden font-bold mr-2">Select:</span>
                     <input
                       type="checkbox"
                       className="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                       checked={selectedItems.some(selectedItem => selectedItem.id === item.id)}
                       onChange={() => handleSelectItem(item)}
                     />
                  </TableCell>
                  {/* Data Cells - rendered differently based on screen size */}
                  {columns.map((column) => (
                    <TableCell
                      key={column.key as string}
                      className="block md:table-cell px-4 py-2 md:px-6 md:py-4 border-b md:border-none break-words min-w-0" // Removed md:whitespace-nowrap
                      data-label={column.label} // Add data-label for mobile view
                    >
                      <span className="md:hidden font-bold mr-2">{column.label}: </span>
                      {/* Special handling for actions column on mobile */}
                      {column.key === 'actions' ? (
                        <div className="flex flex-wrap gap-1 mt-1 md:mt-0"> {/* Ensure actions wrap */}
                          {column.render ? column.render(item) : null}
                        </div>
                      ) : (
                        column.render ? column.render(item) : String(item[column.key as keyof T] ?? '')
                      )}
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

      {selectedItems.length > 0 && (
        <div className="flex items-center justify-between">
          <div>
            <Select value={bulkAction} onValueChange={handleBulkActionChange}>
              <SelectTrigger>
                <SelectValue placeholder="Bulk Actions" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="delete">Delete</SelectItem>
                {/* Add other bulk actions here */}
              </SelectContent>
            </Select>
          </div>
          <Button onClick={handleApplyBulkAction}>Apply</Button>
        </div>
      )}

      <div className="flex items-center justify-between">
        <div className="text-sm text-gray-500 dark:text-gray-400">
          Showing {((data.current_page - 1) * data.per_page) + 1} to {Math.min(data.current_page * data.per_page, data.total)} of {data.total} results
        </div>
        <div className="flex gap-2">
          {data.current_page > 1 && (
            <Button
              variant="outline"
              size="sm"
              onClick={() => router.get(window.location.pathname, { ...filters, page: data.current_page - 1 }, {
                preserveState: true,
                preserveScroll: true,
              })}
            >
              Previous
            </Button>
          )}
          {data.current_page < data.last_page && (
            <Button
              variant="outline"
              size="sm"
              onClick={() => router.get(window.location.pathname, { ...filters, page: data.current_page + 1 }, {
                preserveState: true,
                preserveScroll: true,
              })}
            >
              Next
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}