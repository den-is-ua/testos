<script setup lang="ts">
import { Form, FormField } from "@/components/ui/form";
import FormItem from "@/components/ui/form/FormItem.vue";
import FormLabel from "@/components/ui/form/FormLabel.vue";
import FormControl from "@/components/ui/form/FormControl.vue";
import Input from "@/components/ui/input/Input.vue";
import FormDescription from "@/components/ui/form/FormDescription.vue";
import FormMessage from "@/components/ui/form/FormMessage.vue";
import { useForm, useSetFieldValue } from "vee-validate";
import { ref } from "vue";

const file = ref<File | null>(null)
const uploading = ref(false)
const message = ref<string | null>(null)
const error = ref<string | null>(null)

const getMetaCsrf = () =>
  document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';

function submit() {
  const formData = new FormData()
  formData.append('file', file.value)

  const res = fetch('imports', {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': getMetaCsrf(),
    }
  })

  console.log(res, file)
}

</script>

<template>
    <form id="importForm" @submit.prevent="submit">
        <FormField v-slot="{ componentField }" name="file">
            <FormItem>
                <FormLabel>File</FormLabel>
                <FormControl>
                    <!-- Bind the picked File object to the field -->
                    <Input type="file" accept=".csv,.xls,.xlsx" name="file" @change="(e: Event) => file = (e.target as HTMLInputElement).files?.[0] ?? null" />
                </FormControl>
                <FormDescription>Allowed: CSV, XLS, XLSX. Max 10MB.</FormDescription>
                <FormMessage />
            </FormItem>
        </FormField>
    </form>
</template>