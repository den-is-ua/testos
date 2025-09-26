<script setup lang="ts">
import { importsStore } from "@/stores/progressImportStore";
import Card from "../ui/card/Card.vue";
import CardHeader from "../ui/card/CardHeader.vue";
import CardTitle from "../ui/card/CardTitle.vue";
import Progress from "../ui/progress/Progress.vue";
import CardContent from "../ui/card/CardContent.vue";

const imports = importsStore().getAll();
</script>

<template>
    <Card class="w-[100%] mt-[20px]" v-for="imp in imports">
        <CardHeader>
            <CardTitle>{{ imp.file_name }}</CardTitle>
        </CardHeader>
        <CardContent>
            <Progress v-model="imp.progress" class="w-3/5" />
        </CardContent>
    </Card>
</template>