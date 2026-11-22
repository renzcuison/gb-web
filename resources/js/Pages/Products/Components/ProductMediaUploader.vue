<script setup>
import { ref, onBeforeUnmount } from 'vue'
import { ImagePlus, Star, Trash2 } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  isEdit: {
    type: Boolean,
    default: false
  },
  existingMedia: {
    type: Array,
    default: () => []
  }
})

const fileInput = ref(null)
const previews = ref([])

const visibleExisting = () =>
  props.existingMedia.filter((m) => !(props.form.deleted_media_ids || []).includes(m.id))

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files || [])
  if (!files.length) return

  const targetArray = props.isEdit ? props.form.new_images : props.form.images

  files.forEach((file) => {
    targetArray.push(file)
    previews.value.push({
      name: file.name,
      url: URL.createObjectURL(file)
    })
  })

  if (props.isEdit) {
    if (!props.form.primary_media_id && props.form.primary_new_image_index === null) {
      props.form.primary_new_image_index = 0
    }
  } else if (props.form.primary_image_index === null || props.form.primary_image_index === undefined) {
    props.form.primary_image_index = 0
  }

  event.target.value = ''
}

const setPrimaryExisting = (mediaId) => {
  props.form.primary_media_id = mediaId
  props.form.primary_new_image_index = null
}

const removeExisting = (mediaId) => {
  if (!props.form.deleted_media_ids.includes(mediaId)) {
    props.form.deleted_media_ids.push(mediaId)
  }

  if (props.form.primary_media_id === mediaId) {
    const remaining = visibleExisting()
    if (remaining.length > 0) {
      props.form.primary_media_id = remaining[0].id
    } else if (previews.value.length > 0) {
      props.form.primary_media_id = null
      props.form.primary_new_image_index = 0
    } else {
      props.form.primary_media_id = null
    }
  }
}

const setPrimaryNew = (index) => {
  if (props.isEdit) {
    props.form.primary_media_id = null
    props.form.primary_new_image_index = index
  } else {
    props.form.primary_image_index = index
  }
}

const removeNew = (index) => {
  const targetArray = props.isEdit ? props.form.new_images : props.form.images
  URL.revokeObjectURL(previews.value[index].url)
  targetArray.splice(index, 1)
  previews.value.splice(index, 1)

  if (props.isEdit) {
    if (props.form.primary_new_image_index === index) {
      const remaining = visibleExisting()
      if (remaining.length > 0) {
        props.form.primary_media_id = remaining[0].id
        props.form.primary_new_image_index = null
      } else {
        props.form.primary_new_image_index = previews.value.length > 0 ? 0 : null
      }
    } else if (props.form.primary_new_image_index > index) {
      props.form.primary_new_image_index--
    }
  } else {
    if (props.form.primary_image_index === index) {
      props.form.primary_image_index = previews.value.length > 0 ? 0 : 0
    } else if (props.form.primary_image_index > index) {
      props.form.primary_image_index--
    }
  }
}

const isNewPrimary = (index) => {
  if (props.isEdit) {
    return props.form.primary_media_id === null && props.form.primary_new_image_index === index
  }
  return props.form.primary_image_index === index
}

onBeforeUnmount(() => {
  previews.value.forEach((p) => URL.revokeObjectURL(p.url))
})
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-5">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-zinc-800">
      <div class="flex items-center gap-2">
        <ImagePlus class="h-5 w-5 text-slate-500 dark:text-zinc-400" />
        <div>
          <h2 class="text-base font-semibold text-slate-900 dark:text-zinc-100">Product Media</h2>
          <p class="text-xs text-slate-500 dark:text-zinc-400">
            Upload JPG, PNG, or WEBP images (max 5MB each). Click the star to set the primary thumbnail.
          </p>
        </div>
      </div>

      <div>
        <input
          ref="fileInput"
          type="file"
          multiple
          accept="image/jpeg,image/png,image/webp"
          class="hidden"
          @change="handleFileSelect"
        />
        <Button
          type="button"
          variant="outline"
          size="sm"
          @click="fileInput?.click()"
          class="gap-1.5 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
          <ImagePlus class="h-4 w-4" />
          <span>Upload Images</span>
        </Button>
      </div>
    </div>

    <div
      v-if="(isEdit && visibleExisting().length > 0) || previews.length > 0"
      class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4"
    >
      <div
        v-for="media in visibleExisting()"
        :key="`existing-${media.id}`"
        class="group relative aspect-square rounded-lg border overflow-hidden bg-slate-100 dark:bg-zinc-800"
        :class="form.primary_media_id === media.id ? 'border-amber-500 ring-2 ring-amber-500/30' : 'border-slate-200 dark:border-zinc-700'"
      >
        <img :src="media.url" alt="Product image" class="h-full w-full object-cover" />

        <span
          v-if="form.primary_media_id === media.id"
          class="absolute top-2 left-2 inline-flex items-center gap-1 rounded bg-amber-500 px-1.5 py-0.5 text-[10px] font-semibold text-white shadow"
        >
          Primary
        </span>

        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-black/60 p-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            type="button"
            @click="setPrimaryExisting(media.id)"
            title="Set as primary"
            class="p-1 rounded text-white hover:text-amber-400 transition-colors"
          >
            <Star class="h-4 w-4" :class="form.primary_media_id === media.id ? 'fill-amber-400 text-amber-400' : ''" />
          </button>
          <button
            type="button"
            @click="removeExisting(media.id)"
            title="Remove image"
            class="p-1 rounded text-white hover:text-rose-400 transition-colors"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </div>

      <div
        v-for="(preview, index) in previews"
        :key="`new-${index}`"
        class="group relative aspect-square rounded-lg border overflow-hidden bg-slate-100 dark:bg-zinc-800"
        :class="isNewPrimary(index) ? 'border-amber-500 ring-2 ring-amber-500/30' : 'border-slate-200 dark:border-zinc-700'"
      >
        <img :src="preview.url" :alt="preview.name" class="h-full w-full object-cover" />

        <span
          v-if="isNewPrimary(index)"
          class="absolute top-2 left-2 inline-flex items-center gap-1 rounded bg-amber-500 px-1.5 py-0.5 text-[10px] font-semibold text-white shadow"
        >
          Primary
        </span>

        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-black/60 p-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            type="button"
            @click="setPrimaryNew(index)"
            title="Set as primary"
            class="p-1 rounded text-white hover:text-amber-400 transition-colors"
          >
            <Star class="h-4 w-4" :class="isNewPrimary(index) ? 'fill-amber-400 text-amber-400' : ''" />
          </button>
          <button
            type="button"
            @click="removeNew(index)"
            title="Remove image"
            class="p-1 rounded text-white hover:text-rose-400 transition-colors"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <button
      v-else
      type="button"
      @click="fileInput?.click()"
      class="w-full py-8 border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-xl flex flex-col items-center justify-center gap-2 text-slate-500 dark:text-zinc-400 hover:border-slate-300 dark:hover:border-zinc-700 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors"
    >
      <ImagePlus class="h-6 w-6 text-slate-400 dark:text-zinc-500" />
      <span class="text-xs font-medium">Click to select product photos</span>
    </button>

    <p v-if="form.errors.images || form.errors.new_images" class="text-xs text-rose-500 dark:text-rose-400">
      {{ form.errors.images || form.errors.new_images }}
    </p>
  </div>
</template>