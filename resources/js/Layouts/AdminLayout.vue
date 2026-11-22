<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import RecentActivities from '@/Components/RecentActivities.vue'
import GlobalSearch from '@/Components/GlobalSearch.vue'
import NotificationDropdown from '@/Components/NotificationDropdown.vue'
import UserMenu from '@/Components/UserMenu.vue'
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
  Settings
} from 'lucide-vue-next'

const page = usePage()

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
  { name: 'Inventory', href: '/inventory', icon: Package },
  { name: 'Alerts', href: '/inventory/alerts', icon: Bell },
  { name: 'Announcements', href: '/announcements', icon: Settings },
]

const isCurrentPath = (href) => {
  const currentPath = page.url.split('?')[0]

  if (href === '/inventory') {
    return currentPath === '/inventory'
  }

  return currentPath === href || currentPath.startsWith(`${href}/`)
}
</script>

<template>
  <SidebarProvider>
    <div
      class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 transition-colors duration-200">
      <Sidebar class="border-r border-slate-200 dark:border-zinc-800 dark:bg-zinc-900">
        <!-- Logo Header -->
        <div class="flex h-14 items-center px-4 shrink-0">
          <Link href="/dashboard" class="flex items-center gap-2 transition hover:opacity-90">
            <Logo class="h-8 w-auto" />
          </Link>
        </div>

        <SidebarContent
          class="dark:bg-zinc-900 px-2 py-2 [&::-webkit-scrollbar]:hidden [scrollbar-width:none] [-ms-overflow-style:none]">
          <SidebarGroup>
            <SidebarGroupLabel
              class="text-slate-400 dark:text-zinc-500 font-semibold text-[11px] tracking-wider uppercase px-3 mb-2">
              Management
            </SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu class="space-y-1">
                <SidebarMenuItem v-for="item in navigation" :key="item.name">
                  <SidebarMenuButton as-child :is-active="isCurrentPath(item.href)"
                    class="transition-all duration-150 rounded-md" :class="[
                      isCurrentPath(item.href)
                        ? '!bg-slate-200 !text-slate-900 dark:!bg-zinc-800 dark:!text-zinc-100 font-semibold'
                        : 'text-slate-600 hover:!bg-slate-100 hover:!text-slate-900 dark:text-zinc-400 dark:hover:!bg-zinc-800/60 dark:hover:!text-zinc-100'
                    ]">
                    <Link :href="item.href"
                      class="flex items-center gap-3 px-3 py-2 text-[13px] font-medium tracking-tight">
                      <component :is="item.icon" class="h-4 w-4 shrink-0 transition-colors" :class="[
                        isCurrentPath(item.href)
                          ? '!text-slate-900 dark:!text-zinc-100'
                          : 'text-slate-400 dark:text-zinc-500 group-hover:text-slate-700 dark:group-hover:text-zinc-200'
                      ]" />
                      <span>{{ item.name }}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>
      </Sidebar>

      <SidebarInset class="flex flex-col flex-1 h-screen overflow-hidden dark:bg-zinc-950">
        <header
          class="flex h-14 shrink-0 items-center justify-between border-b border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-6 relative z-50">

          <div class="flex items-center gap-3">
            <SidebarTrigger />
            <RecentActivities />
            <GlobalSearch />
          </div>

          <div class="flex items-center gap-2 sm:gap-3">
            <NotificationDropdown />
            <UserMenu />
          </div>
        </header>

        <main
          class="flex-1 overflow-y-auto p-6 md:p-8 [&::-webkit-scrollbar]:hidden [scrollbar-width:none] [-ms-overflow-style:none]">
          <slot />
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>