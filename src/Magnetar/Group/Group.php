<?php
	declare(strict_types=1);
	
	namespace Magnetar\Group;
	
	use ArrayAccess;
	use ArrayIterator;
	use Countable;
	
	class Group implements ArrayAccess, Countable {
		/**
		 * Array of items in the group
		 * @var array
		 */
		protected array $items = [];
		
		/**
		 * Constructor
		 */
		public function __construct(
			array $items=[]
		) {
			$this->items = $this->sanitizeArray($items);
		}
		
		/**
		 * Sanitize a group-like instance into a simple array
		 * @param mixed $items Items to sanitize
		 * @return array
		 */
		protected function sanitizeArray(mixed $items): array {
			if(is_array($items)) {
				return $items;
			}
			
			// class-specific sanitization
			return match(true) {
				$items instanceof \Magnetar\Group\Group => $items->all(),
				default => (array)$items,
			};
		}
		
		/**
		 * Get the items in the group
		 * @return array
		 */
		public function all(): array {
			return $this->items;
		}
		
		/**
		 * Get the number of items in the group
		 * @return int
		 */
		public function count(): int {
			return count($this->items);
		}
		
		/**
		 * Get the first item in the group. Returns null if the group is empty
		 * @return mixed
		 */
		public function first(): mixed {
			return $this->items[0] ?? null;
		}
		
		/**
		 * Get the last item in the group
		 * @return mixed
		 */
		public function last(): mixed {
			return $this->items[count($this->items) - 1] ?? null;
		}
		
		/**
		 * Get the item at the specified index. Returns null if the index is out of bounds
		 * @param int $index Index of the item to get
		 * @return mixed
		 */
		public function get(int $index): mixed {
			return $this->items[ $index ] ?? null;
		}
		
		/**
		 * Get the item at the specified index, or set it if it doesn't exist
		 * @param int $index Index of the item to get
		 * @param mixed $item Item to set if the index doesn't exist
		 * @return mixed
		 */
		public function getOrSet(int $index, mixed $item): mixed {
			if(!isset($this->items[ $index ])) {
				$this->items[ $index ] = $item;
			}
			
			return $this->items[ $index ];
		}
		
		/**
		 * Set the item at the specified index
		 * @param int $index Index of the item to set
		 * @param mixed $item Item to set
		 * @return void
		 */
		public function set(int $index, mixed $item): void {
			$this->items[ $index ] = $item;
		}
		
		/**
		 * Add an item to the group
		 * @param mixed $item Item to add
		 * @return void
		 */
		public function add(mixed $item): void {
			$this->items[] = $item;
		}
		
		/**
		 * Remove an item from the group by index
		 * @param int $index Index of the item
		 * @return void
		 */
		public function forget(int $index): void {
			unset($this->items[ $index ]);
		}
		
		/**
		 * Determine if the group is empty
		 * @return bool
		 */
		public function isEmpty(): bool {
			return empty($this->items);
		}
		
		/**
		 * Get the keys of the items in the group
		 * @return static
		 */
		public function keys(): static {
			return new static(array_keys($this->items));
		}
		
		/**
		 * Get the values of the items in the group
		 * @return static
		 */
		public function values(): static {
			return new static(array_values($this->items));
		}
		
		/**
		 * Pluck values from the items in the group by a shared key
		 * @param string $key
		 * @return static
		 */
		public function pluck(string $key): static {
			$values = [];
			
			foreach($this->items as $item) {
				if(isset($item[ $key ])) {
					$values[] = $item[ $key ];
				}
			}
			
			return new static($values);
		}
		
		/**
		 * Make a new group with the keys and values flipped
		 * @return static
		 */
		public function flip(): static {
			return new static(array_flip($this->items));
		}
		
		/**
		 * Apply a callback to each item in the group
		 * @param callable $callback Callback to apply
		 * @return static
		 */
		public function map(callable $callback): static {
			return new static(array_map($callback, $this->items));
		}
		
		/**
		 * Reduce the group to a single value
		 * @param callable $callback Callback to reduce the group
		 * @param mixed $initial Initial value to reduce with
		 * @return mixed
		 */
		public function reduce(callable $callback, mixed $initial=null): mixed {
			return array_reduce($this->items, $callback, $initial);
		}
		
		/**
		 * Merge the group with the given items
		 * @param mixed $newItems Items to merge
		 * @return static
		 */
		public function merge(mixed $newItems): static {
			return new static(array_merge($this->items, $this->sanitizeArray($newItems)));
		}
		
		/**
		 * Pop the last item off the group and return it
		 * @return mixed
		 */
		public function pop(): mixed {
			return array_pop($this->items);
		}
		
		/**
		 * Push an item onto the end of the group
		 * @param mixed $item Item to push
		 * @return void
		 */
		public function push(mixed $item): void {
			$this->items[] = $item;
		}
		
		/**
		 * Reverse the order of the items in the group
		 * @return static
		 */
		public function reverse(): static {
			return new static(array_reverse($this->items));
		}
		
		/**
		 * Shift the first item off the group and return it
		 * @return mixed
		 */
		public function shift(): mixed {
			return array_shift($this->items);
		}
		
		/**
		 * Prepend an item to the group
		 * @param mixed $item Item to prepend
		 * @return void
		 */
		public function unshift(mixed $item): void {
			array_unshift($this->items, $item);
		}
		
		/**
		 * Slice the group
		 * @param int $offset Offset to start at
		 * @param int|null $length Length of the slice
		 * @return static
		 */
		public function slice(int $offset, int $length=null): static {
			return new static(array_slice($this->items, $offset, $length));
		}
		
		/**
		 * Take the first $size items from the group
		 * @param int $size Number of items to take
		 * @return static
		 */
		public function take(int $size): static {
			return $this->slice(0, $size);
		}
		
		/**
		 * Chunk the group
		 * @param int $size Size of each chunk
		 * @return static
		 */
		public function chunk(int $size): static {
			return new static(array_chunk($this->items, $size));
		}
		
		/**
		 * Pad the group to the given size with the given value
		 * @param int $size Number of items to take
		 * @param mixed $value Value to pad with
		 * @return static
		 */
		public function pad(int $size, mixed $value): static {
			return new static(array_pad($this->items, $size, $value));
		}
		
		/**
		 * Splice the group
		 * @param int $offset Offset to start at
		 * @param int|null $length Length of the splice
		 * @param mixed $replacement Replacement items
		 * @return static
		 */
		public function splice(int $offset, int $length=null, mixed $replacement=[]): static {
			return new static(array_splice($this->items, $offset, $length, $replacement));
		}
		
		/**
		 * Shuffle the group randomly
		 * @return static
		 */
		public function shuffle(): static {
			$items = $this->items;
			
			shuffle($items);
			
			return new static($items);
		}
		
		/**
		 * Sort the group
		 * @param callable|null $callback Callback to sort the group
		 * @return static
		 * 
		 * @TODO
		 */
		public function sort(callable $callback=null): static {
			$items = $this->items;
			
			if(is_null($callback)) {
				sort($items);
			} else {
				usort($items, $callback);
			}
			
			return new static($items);
		}
		
		/**
		 * Get the items in the group as a string joined by the given glue
		 * @param string $glue Glue to join the items with
		 * @return string
		 */
		public function implode(string $glue=','): string {
			return implode($glue, $this->items);
		}
		
		/**
		 * Get the items in the group as a JSON string
		 * @param int $options JSON options from json_encode()
		 * @return string
		 * 
		 * @see \json_encode()
		 */
		public function toJson(int $options=0): string {
			return json_encode($this->items, $options);
		}
		
		/**
		 * Get the items in the group as a serialized string
		 * @return string
		 */
		public function toSerialize(): string {
			return serialize($this->items);
		}
		
		
		public function getIterator(): ArrayIterator {
			return new ArrayIterator($this->items);
		}
		
		/**
		 * Determine if a given offset exists
		 * @param mixed $key The offset to check
		 * @return bool Whether the offset exists
		 */
		public function offsetExists(mixed $key): bool {
			return isset($this->items[ $key ]);
		}
		
		/**
		 * Get the value at a given offset
		 * @param mixed $key The offset to get
		 * @return mixed The value at the offset
		 */
		public function offsetGet(mixed $key): mixed {
			return $this->items[ $key ];
		}
		
		/**
		 * Set the value at a given offset
		 * @param mixed $key The offset to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function offsetSet(mixed $key, mixed $value): void {
			if(is_null($key)) {
				$this->items[] = $value;
			} else {
				$this->items[ $key ] = $value;
			}
		}
		
		/**
		 * Unset the value at a given offset
		 * @param string $key The offset to unset
		 * @return void
		 */
		public function offsetUnset(mixed $key): void {
			unset($this->items[ $key ]);
		}
	}