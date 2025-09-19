<script setup lang="ts">
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed, toRefs } from 'vue';

interface Props {
    name: string;
    class?: string;
    size?: number | string;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2,
});

const { size, strokeWidth, color } = toRefs(props);

const className = computed(() => cn('h-4 w-4', props.class));

const icon = computed(() => {
    const toPascal = (str: string) =>
        str
            .split(/[-_ ]+/)
            .map(s => s.charAt(0).toUpperCase() + s.slice(1))
            .join('');

    const iconName = toPascal(props.name);
    const matched = (icons as Record<string, any>)[iconName];
    return matched ?? null;
});
</script>

<template>
    <component
        :is="icon"
        :class="className"
        :size="size"
        :stroke-width="strokeWidth"
        :color="color"
    />
</template>
