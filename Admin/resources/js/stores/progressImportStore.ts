import { Import } from '@/types';
import { reactive, computed } from 'vue'

const state = reactive({ items: [] as Import[] })

export function imports() {
  const count = computed(() => state.items.length)
  function add(item: Import) { state.items.push(item) }
  function remove(importId: number) {
    const i = state.items.findIndex(x => x.id === importId)
    if (i !== -1) state.items.splice(i, 1)
  }
  function clear() { state.items = [] }
  return { state, count, add, clear, remove }
}