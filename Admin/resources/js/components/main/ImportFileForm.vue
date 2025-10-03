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
import Button from "../ui/button/Button.vue";
import { toast } from "vue-sonner"
import Toaster from "./Toaster.vue";
import { boolean } from "zod/v4";
import { importsStore } from "@/stores/progressImportStore";
import { Import } from "@/types";

const file = ref<File | null>(null)

const emit = defineEmits<{ 'form:sentWithSucccess':[boolean] }>()

const getMetaCsrf = () =>
  document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';

async function submit() {
  const formData = new FormData()
  formData.append('file', file.value)

  const res = await fetch('imports', {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': getMetaCsrf(),
      'Accept': 'application/json'
    }
  })

  const data = await res.json()

  if (res.status === 422) {
    console.log(data)
    toast.warning(data.message)
    return
  }

  if (res.status >= 500) {
    toast.error('Something went wrong')
    return
  }

  importsStore().add(data.data as Import)
  toast.success(data.message)
  emit('form:sentWithSucccess', true)
}

</script>

<template>
    <form id="importForm" @submit.prevent="submit">
        <FormField v-slot="{ componentField }" name="file">
            <FormItem>
                <FormLabel>File</FormLabel>
                <FormControl>
                    <!-- Bind the picked File object to the field -->
                    <Input type="file" accept=".csv" name="file" @change="(e: Event) => file = (e.target as HTMLInputElement).files?.[0] ?? null" />
                </FormControl>
                <FormDescription>Allowed: CSV. Max 10MB.</FormDescription>
                <FormMessage />
            </FormItem>
        </FormField>
        <Button type="submit" form="importForm" class="mt-[20px] float-right">
            Upload
        </Button>
    </form>
</template>