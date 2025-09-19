<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { Progress } from "@/components/ui/progress";
import { ref, onMounted, computed } from "vue";
import { FormField } from "@/components/ui/form";
import FormItem from "@/components/ui/form/FormItem.vue";
import FormLabel from "@/components/ui/form/FormLabel.vue";
import FormControl from "@/components/ui/form/FormControl.vue";
import Input from "@/components/ui/input/Input.vue";
import FormDescription from "@/components/ui/form/FormDescription.vue";
import FormMessage from "@/components/ui/form/FormMessage.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";

type Invoice = {
    invoice: string;
    paymentStatus: string;
    totalAmount: string;
    paymentMethod: string;
};

const items = ref<Invoice[]>([]);
const page = ref(1);
const perPage = ref(5);
const total = ref(0);
const lastPage = ref(1);
const loading = ref(false);
const progress = ref(32);

async function fetchInvoices() {
    loading.value = true;
    try {
        const params = new URLSearchParams({ page: String(page.value), per_page: String(perPage.value) });
        const res = await fetch(`/?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        items.value = data.data || [];
        total.value = data.total || 0;
        lastPage.value = data.last_page || 1;
    } catch (e) {
        console.error("Failed to load invoices", e);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchInvoices();
});

function goTo(p: number) {
    if (p < 1 || p > lastPage.value) return;
    page.value = p;
    fetchInvoices();
}

const pages = computed(() => {
    const arr: number[] = [];
    for (let p = 1; p <= lastPage.value; p++) arr.push(p);
    return arr;
});
</script>

<template>
    <div class="flex">
        <div class="w-[60%] m-20">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[100px]">
                            Invoice
                        </TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead class="text-right">
                            Amount
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="!loading && items.length === 0">
                        <TableCell colspan="4" class="text-center py-6">No invoices found.</TableCell>
                    </TableRow>

                    <TableRow v-for="invoice in items" :key="invoice.invoice">
                        <TableCell class="font-medium">
                            {{ invoice.invoice }}
                        </TableCell>
                        <TableCell>{{ invoice.paymentStatus }}</TableCell>
                        <TableCell>{{ invoice.paymentMethod }}</TableCell>
                        <TableCell class="text-right">
                            {{ invoice.totalAmount }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="flex items-center w-[100%] justify-center mt-[20px]">
                <div class="flex items-center space-x-2">
                    <button class="btn" :disabled="page === 1 || loading" @click="goTo(page - 1)">
                        Prev
                    </button>

                    <button v-for="p in pages" :key="p" :class="['btn', { 'btn-primary': p === page }]"
                        @click="goTo(p)">
                        {{ p }}
                    </button>

                    <button class="btn" :disabled="page === lastPage || loading" @click="goTo(page + 1)">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <div class="w-[40%] m-20">
            <FormField name="username" v-slot="{ field }">
                <FormItem>
                    <FormLabel>Username</FormLabel>
                    <FormControl>
                        <Input placeholder="shadcn" v-bind="field" />
                    </FormControl>
                    <FormDescription />
                    <FormMessage />
                </FormItem>
            </FormField>
            <Card class="w-[100%]">
                <CardHeader>
                    <CardTitle>Import name</CardTitle>
                </CardHeader>
                <CardContent>
                    <Progress v-model="progress" class="w-3/5" />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
