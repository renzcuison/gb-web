<script setup>
import { ref, computed } from 'vue'
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
  MessageSquare,
  Megaphone,
  ChevronDown
} from 'lucide-vue-next'

const page = usePage()
const openMenus = ref({
  Inventory: true,
})

const toggleMenu = (name) => {
  openMenus.value[name] = !openMenus.value[name]
}

const adminBadges = computed(() => page.props.admin_badges || { unresolved_alerts: 0, new_inquiries: 0 })

const navGroups = [
  {
    label: 'Management',
    items: [
      { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
      {
        name: 'Inventory',
        href: '/inventory',
        icon: Package,
        children: [
          { name: 'Stock Inventory', href: '/inventory' },
          { name: 'Movement Logs', href: '/inventory/movements' },
          { name: 'Categories', href: '/categories' },
          { name: 'Brands', href: '/brands' },
          { name: 'Tags', href: '/tags' },
        ],
      },
      { name: 'Alerts', href: '/inventory/alerts', icon: Bell, badgeKey: 'unresolved_alerts' },
      { name: 'Inquiries', href: '/inquiries', icon: MessageSquare, badgeKey: 'new_inquiries' },
      { name: 'Announcements', href: '/announcements', icon: Megaphone },
    ]
  }
]

const getCurrentPath = () => page.url.split('?')[0]

const isChildActive = (child) => {
  const currentPath = getCurrentPath()

  if (child.href === '/inventory') {
    return currentPath === '/inventory' || currentPath.startsWith('/products')
  }

  return currentPath === child.href || currentPath.startsWith(`${child.href}/`)
}

const isItemActive = (item) => {
  const currentPath = getCurrentPath()

  if (item.children) {
    return item.children.some((child) => isChildActive(child))
  }

  return currentPath === item.href || currentPath.startsWith(`${item.href}/`)
}
</script>

<template>
  <SidebarProvider>
    <div
      class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 transition-colors duration-200">
      <Sidebar collapsible="icon" class="border-r border-slate-200 dark:border-zinc-800 dark:bg-zinc-900">
        <div
          class="flex h-14 items-center px-4 group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:justify-center shrink-0">
          <Link href="/dashboard"
            class="flex items-center gap-2 transition hover:opacity-90 overflow-hidden group-data-[collapsible=icon]:hidden">
            <Logo class="h-8 w-auto shrink-0" />
          </Link>
        </div>

        <SidebarContent
          class="dark:bg-zinc-900 px-2 group-data-[collapsible=icon]:px-1.5 py-2 [&::-webkit-scrollbar]:hidden [scrollbar-width:none] [-ms-overflow-style:none]">
          <SidebarGroup v-for="group in navGroups" :key="group.label" class="group-data-[collapsible=icon]:px-0">
            <SidebarGroupLabel
              class="text-slate-400 dark:text-zinc-500 font-semibold text-[11px] tracking-wider uppercase px-3 mb-2 group-data-[collapsible=icon]:hidden">
              {{ group.label }}
            </SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu class="space-y-1">
                <SidebarMenuItem v-for="item in group.items" :key="item.name">
                  <!-- Parent Item with Collapsible Children -->
                  <template v-if="item.children">
                    <SidebarMenuButton :is-active="isItemActive(item)" class="transition-all duration-150 rounded-md"
                      :class="[
                        isItemActive(item)
                          ? 'text-slate-900 dark:text-zinc-100 font-semibold hover:!bg-slate-100 dark:hover:!bg-zinc-800/60'
                          : 'text-slate-600 hover:!bg-slate-100 hover:!text-slate-900 dark:text-zinc-400 dark:hover:!bg-zinc-800/60 dark:hover:!text-zinc-100'
                      ]">
                      <div :title="item.name"
                        class="flex items-center justify-between w-full px-3 py-2 text-[13px] font-medium tracking-tight group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:justify-center">
                        <Link :href="item.href" @click="openMenus[item.name] = true"
                          class="flex flex-1 items-center gap-3 min-w-0 group-data-[collapsible=icon]:flex-initial group-data-[collapsible=icon]:justify-center">
                          <component :is="item.icon" class="h-4 w-4 shrink-0 transition-colors" :class="[
                            isItemActive(item)
                              ? 'text-slate-900 dark:text-zinc-100'
                              : 'text-slate-400 dark:text-zinc-500'
                          ]" />
                          <span class="truncate group-data-[collapsible=icon]:hidden">{{ item.name }}</span>
                        </Link>

                        <button type="button" @click.stop="toggleMenu(item.name)"
                          :aria-expanded="Boolean(openMenus[item.name])" :aria-label="`Toggle ${item.name} submenu`"
                          class="p-0.5 rounded text-slate-400 hover:text-slate-700 dark:text-zinc-500 dark:hover:text-zinc-200 transition-colors group-data-[collapsible=icon]:hidden">
                          <ChevronDown class="h-3.5 w-3.5 transition-transform duration-150"
                            :class="openMenus[item.name] ? 'rotate-0' : '-rotate-90'" />
                        </button>
                      </div>
                    </SidebarMenuButton>

                    <div v-show="openMenus[item.name]"
                      class="ml-5 mt-1 pl-2.5 border-l border-slate-200 dark:border-zinc-800 space-y-0.5 group-data-[collapsible=icon]:hidden">
                      <Link v-for="child in item.children" :key="child.name" :href="child.href"
                        class="flex items-center rounded-md px-2.5 py-1.5 text-[12.5px] tracking-tight transition-colors"
                        :class="[
                          isChildActive(child)
                            ? 'bg-slate-200/90 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100 font-semibold'
                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-zinc-400 dark:hover:bg-zinc-800/60 dark:hover:text-zinc-100 font-medium'
                        ]">
                        {{ child.name }}
                      </Link>
                    </div>
                  </template>

                  <SidebarMenuButton v-else :is-active="isItemActive(item)"
                    class="transition-all duration-150 rounded-md" :class="[
                      isItemActive(item)
                        ? '!bg-slate-200 !text-slate-900 dark:!bg-zinc-800 dark:!text-zinc-100 font-semibold'
                        : 'text-slate-600 hover:!bg-slate-100 hover:!text-slate-900 dark:text-zinc-400 dark:hover:!bg-zinc-800/60 dark:hover:!text-zinc-100'
                    ]">
                    <Link :href="item.href" :title="item.name"
                      class="flex items-center justify-between gap-2 px-3 py-2 text-[13px] font-medium tracking-tight group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:justify-center">
                      <span class="flex items-center gap-3 min-w-0 group-data-[collapsible=icon]:justify-center">
                        <component :is="item.icon" class="h-4 w-4 shrink-0 transition-colors" :class="[
                          isItemActive(item)
                            ? '!text-slate-900 dark:!text-zinc-100'
                            : 'text-slate-400 dark:text-zinc-500 group-hover:text-slate-700 dark:group-hover:text-zinc-200'
                        ]" />
                        <span class="truncate group-data-[collapsible=icon]:hidden">{{ item.name }}</span>
                      </span>

                      <span v-if="item.badgeKey && adminBadges[item.badgeKey] > 0"
                        class="font-mono text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-200/80 text-slate-800 dark:bg-zinc-800 dark:text-zinc-200 group-data-[collapsible=icon]:hidden">
                        {{ adminBadges[item.badgeKey] > 99 ? '99+' : adminBadges[item.badgeKey] }}
                      </span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>

        <div
          class="mt-auto flex items-center justify-end p-3 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0 shrink-0">
          <SidebarTrigger
            class="hidden md:inline-flex shrink-0 border-0 bg-transparent hover:bg-transparent text-slate-400 hover:text-slate-900 dark:text-zinc-500 dark:hover:text-zinc-100" />
        </div>
      </Sidebar>

      <SidebarInset class="flex flex-col flex-1 h-screen overflow-hidden dark:bg-zinc-950">
        <header
          class="flex h-14 shrink-0 items-center justify-between border-b border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-6 relative z-50">

          <div class="flex items-center gap-3">
            <SidebarTrigger class="md:hidden" />
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