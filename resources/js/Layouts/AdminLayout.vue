<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  SidebarProvider,
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarTrigger,
  SidebarInset
} from '@/Components/ui/sidebar'
import {
  LayoutDashboard,
  Package,
  Bell,
  Settings,
  LogOut
} from 'lucide-vue-next'

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
  { name: 'Inventory', href: '/inventory', icon: Package },
  { name: 'Alerts', href: '/inventory/alerts', icon: Bell },
  { name: 'Announcements', href: '/announcements', icon: Settings },
]

const isCurrentPath = (href) => {
  return page.url.startsWith(href)
}
</script>

<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-50">
      <!-- Sidebar Navigation -->
      <Sidebar>
        <div class="flex h-14 items-center border-b px-4 font-bold text-slate-900 tracking-tight">
          IMS Admin
        </div>
        <SidebarContent>
          <SidebarGroup>
            <SidebarGroupLabel>Management</SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu>
                <SidebarMenuItem v-for="item in navigation" :key="item.name">
                  <SidebarMenuButton as-child :is-active="isCurrentPath(item.href)">
                    <Link :href="item.href" class="flex items-center gap-3 px-3 py-2 text-sm font-medium">
                      <component :is="item.icon" class="h-4 w-4" />
                      <span>{{ item.name }}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>
      </Sidebar>

      <SidebarInset class="flex flex-col flex-1">
        <header class="flex h-14 items-center justify-between border-b bg-white px-6 shadow-sm">
          <div class="flex items-center gap-4">
            <SidebarTrigger />
            <h2 class="text-sm font-semibold text-slate-700">Inventory Management System</h2>
          </div>
          <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-slate-700">{{ authUser?.name || 'Staff User' }}</span>
            <Link
              href="/logout"
              method="post"
              as="button"
              class="flex items-center gap-1 text-sm font-medium text-rose-600 hover:text-rose-700"
            >
              <LogOut class="h-4 w-4" />
              <span class="hidden sm:inline">Logout</span>
            </Link>
          </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6 md:p-8">
          <slot />
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>