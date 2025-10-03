<script lang="ts" setup>
import { PaginationItem } from '@/types';


const props = defineProps<{
  links: PaginationItem[]
  loading: boolean
}>()

const emit = defineEmits(['change'])

</script>

<template>
<div class="flex items-center w-[100%] justify-center mt-[20px]" v-if="$props.links.length > 0">
        <div class="flex items-center space-x-2">
            <button v-for="link in links" :class="['btn']" :key="link.label" :disabled="$props.loading || link.active === true" @click="emit('change', link.page)">
                <span v-html="link.label"></span>
            </button>
        </div>
    </div>
</template>
