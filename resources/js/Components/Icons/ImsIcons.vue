<script>
import { defineComponent, h } from 'vue'

const createImsIcon = (name, paths, defaultClass = 'h-5 w-5') =>
  defineComponent({
    name,
    props: {
      class: { type: String, default: defaultClass },
    },
    setup(props) {
      return () =>
        h(
          'svg',
          {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '2.75 2.75 18.5 18.5',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '1.25',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: props.class,
          },
          paths.map(([tag, attrs]) => h(tag, attrs))
        )
    },
  })

export const ImsDashboardIcon = createImsIcon('ImsDashboardIcon', [
  ['path', { d: 'M4 6.5h16' }],
  ['path', { d: 'M4 12h9' }],
  ['path', { d: 'M16 12h4' }],
  ['path', { d: 'M4 17.5h5' }],
  ['path', { d: 'M12 17.5h8' }],
])

export const ImsInventoryIcon = createImsIcon('ImsInventoryIcon', [
  ['path', { d: 'M5.5 8.5h13l.8 11H4.7l.8-11Z' }],
  ['path', { d: 'M9 8.5V6.5a3 3 0 0 1 6 0v2' }],
])

export const ImsAlertsIcon = createImsIcon('ImsAlertsIcon', [
  ['path', { d: 'M7 16V10.5a5 5 0 0 1 10 0V16' }],
  ['path', { d: 'M4.5 16h15' }],
  ['path', { d: 'M10.5 19.5h3' }],
])

export const ImsInquiriesIcon = createImsIcon('ImsInquiriesIcon', [
  ['path', { d: 'M4.5 5.5h15v10h-9l-4 3.5v-3.5h-2v-10Z' }],
  ['path', { d: 'M8.5 10.5h7' }],
])

export const ImsAnnouncementsIcon = createImsIcon('ImsAnnouncementsIcon', [
  ['path', { d: 'M4 10h4l7-4.5v13L8 14H4v-4Z' }],
  ['path', { d: 'M18.5 9.5v5' }],
])

export const ImsUsersIcon = createImsIcon('ImsUsersIcon', [
  ['path', { d: 'M6.5 8a3 3 0 1 1 6 0 3 3 0 0 1-6 0' }],
  ['path', { d: 'M4 19c.6-2.8 2.8-4.5 5.5-4.5s4.9 1.7 5.5 4.5' }],
  ['path', { d: 'M16.5 6.5a2.5 2.5 0 0 1 0 5' }],
  ['path', { d: 'M18 15c1.3.7 2.2 2 2.5 4' }],
])

export const ImsSidebarPanelIcon = createImsIcon('ImsSidebarPanelIcon', [
  ['rect', { x: '4', y: '5', width: '16', height: '14', rx: '2', 'stroke-width': '1' }],
  ['path', { d: 'M9.5 5v14', 'stroke-width': '1' }],
])

export const ImsChevronDownIcon = createImsIcon('ImsChevronDownIcon', [
  ['path', { d: 'M6.5 9.5 12 15l5.5-5.5' }],
])

export const ImsChevronRightIcon = createImsIcon('ImsChevronRightIcon', [
  ['path', { d: 'M9.5 6.5 15 12l-5.5 5.5' }],
])

export const ImsChevronLeftIcon = createImsIcon('ImsChevronLeftIcon', [
  ['path', { d: 'M14.5 6.5 9 12l5.5 5.5' }],
])

export const ImsArrowUpRightIcon = createImsIcon('ImsArrowUpRightIcon', [
  ['path', { d: 'M7 17 17 7' }],
  ['path', { d: 'M9.5 7H17v7.5' }],
])

export const ImsArrowLeftIcon = createImsIcon('ImsArrowLeftIcon', [
  ['path', { d: 'M19 12H5' }],
  ['path', { d: 'M10.5 6.5 5 12l5.5 5.5' }],
])

export const ImsCloseIcon = createImsIcon('ImsCloseIcon', [
  ['path', { d: 'M6.5 6.5 17.5 17.5' }],
  ['path', { d: 'M17.5 6.5 6.5 17.5' }],
])

export const ImsMenuIcon = createImsIcon('ImsMenuIcon', [
  ['path', { d: 'M4 8h16' }],
  ['path', { d: 'M4 16h11' }],
])

export const ImsRefreshIcon = createImsIcon('ImsRefreshIcon', [
  ['path', { d: 'M19.5 11.5A7.5 7.5 0 1 0 17.5 17' }],
  ['path', { d: 'M19.5 6v5.5H14' }],
])

export const ImsActivityClockIcon = createImsIcon('ImsActivityClockIcon', [
  ['path', { d: 'M12 4a8 8 0 1 1-8 8' }],
  ['path', { d: 'M12 8v4l3 2' }],
])

export const ImsBellIcon = createImsIcon('ImsBellIcon', [
  ['path', { d: 'M7 16V10.5a5 5 0 0 1 10 0V16' }],
  ['path', { d: 'M4.5 16h15' }],
  ['path', { d: 'M10.5 19.5h3' }],
])

export const ImsBellRingIcon = createImsIcon('ImsBellRingIcon', [
  ['path', { d: 'M7 16V10.5a5 5 0 0 1 10 0V16' }],
  ['path', { d: 'M4.5 16h15' }],
  ['path', { d: 'M10.5 19.5h3' }],
  ['path', { d: 'M4.5 7.5 6.5 5.5M19.5 7.5l-2-2' }],
])

export const ImsCheckDoubleIcon = createImsIcon('ImsCheckDoubleIcon', [
  ['path', { d: 'M3.5 12.5 8 17 16.5 7.5' }],
  ['path', { d: 'M11.5 17 13 18.5 20.5 9.5' }],
])

export const ImsStatusDangerIcon = createImsIcon('ImsStatusDangerIcon', [
  ['path', { d: 'M12 4 20.5 19H3.5L12 4Z' }],
  ['path', { d: 'M12 10v4' }],
  ['path', { d: 'M12 16.5v.3' }],
])

export const ImsStatusWarningIcon = createImsIcon('ImsStatusWarningIcon', [
  ['path', { d: 'M12 4a8 8 0 1 1 0 16 8 8 0 0 1 0-16Z' }],
  ['path', { d: 'M12 8.5v4.5' }],
  ['path', { d: 'M12 15.8v.3' }],
])

export const ImsStatusSuccessIcon = createImsIcon('ImsStatusSuccessIcon', [
  ['path', { d: 'M19.5 10.5A7.5 7.5 0 1 1 15 4.8' }],
  ['path', { d: 'M9 12l2.5 2.5L20 6' }],
])

export const ImsStatusInfoIcon = createImsIcon('ImsStatusInfoIcon', [
  ['path', { d: 'M12 4a8 8 0 1 1 0 16 8 8 0 0 1 0-16Z' }],
  ['path', { d: 'M12 11.5v4.5' }],
  ['path', { d: 'M12 8.5v.3' }],
])

export const ImsSearchIcon = createImsIcon('ImsSearchIcon', [
  ['path', { d: 'M11 4.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Z' }],
  ['path', { d: 'M16 16l4 4' }],
])

export const ImsTagIcon = createImsIcon('ImsTagIcon', [
  ['path', { d: 'M4.5 4.5h7L20 13l-7 7-8.5-8.5v-7Z' }],
  ['path', { d: 'M8.5 8.5h.3' }],
])

export const ImsBrandIcon = createImsIcon('ImsBrandIcon', [
  ['path', { d: 'M4.5 6h15' }],
  ['path', { d: 'M4.5 12h10' }],
  ['path', { d: 'M4.5 18h6' }],
])

export const ImsBrandMarkIcon = createImsIcon(
  'ImsBrandMarkIcon',
  [
    ['path', { d: 'M4 4h16v16H4V4Z' }],
    ['path', { d: 'M8.5 9.5h7' }],
    ['path', { d: 'M12 9.5v6' }],
  ],
  'h-9 w-9'
)

export const ImsUserIcon = createImsIcon('ImsUserIcon', [
  ['path', { d: 'M8.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0' }],
  ['path', { d: 'M5 19.5c.7-3.2 3.4-5 7-5s6.3 1.8 7 5' }],
])

export const ImsSettingsIcon = createImsIcon('ImsSettingsIcon', [
  ['path', { d: 'M4 7h16' }],
  ['path', { d: 'M9 4.5v5' }],
  ['path', { d: 'M4 17h16' }],
  ['path', { d: 'M15 14.5v5' }],
])

export const ImsBugIcon = createImsIcon('ImsBugIcon', [
  ['path', { d: 'M8 8.5a4 4 0 0 1 8 0v6a4 4 0 0 1-8 0v-6Z' }],
  ['path', { d: 'M5 11.5h3M16 11.5h3M5 15.5h3M16 15.5h3' }],
])

export const ImsSunIcon = createImsIcon('ImsSunIcon', [
  ['path', { d: 'M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z' }],
  ['path', { d: 'M12 3.5v1.5M12 19v1.5M3.5 12H5M19 12h1.5' }],
])

export const ImsMoonIcon = createImsIcon('ImsMoonIcon', [
  ['path', { d: 'M19 14.5A7 7 0 0 1 9.5 5a7 7 0 1 0 9.5 9.5Z' }],
])

export const ImsSignOutIcon = createImsIcon('ImsSignOutIcon', [
  ['path', { d: 'M10 4.5H5.5v15H10' }],
  ['path', { d: 'M14.5 8.5 18.5 12l-4 3.5' }],
  ['path', { d: 'M18.5 12H9.5' }],
])

export const ImsCheckIcon = createImsIcon('ImsCheckIcon', [
  ['path', { d: 'M5.5 12.5 10 17 19 7.5' }],
])

export const ImsCircleDotIcon = createImsIcon('ImsCircleDotIcon', [
  ['path', { d: 'M9.5 12h5' }],
  ['path', { d: 'M12 9.5v5' }],
])

export const ImsPlusIcon = createImsIcon('ImsPlusIcon', [
  ['path', { d: 'M12 5v14' }],
  ['path', { d: 'M5 12h14' }],
])

export const ImsPencilIcon = createImsIcon('ImsPencilIcon', [
  ['path', { d: 'M15.5 4.5 19.5 8.5 8.5 19.5H4.5v-4L15.5 4.5Z' }],
])

export const ImsTrashIcon = createImsIcon('ImsTrashIcon', [
  ['path', { d: 'M4.5 7h15' }],
  ['path', { d: 'M9.5 7V4.5h5V7' }],
  ['path', { d: 'M6.5 7l.8 12.5h9.4L17.5 7' }],
])

export const ImsDownloadIcon = createImsIcon('ImsDownloadIcon', [
  ['path', { d: 'M12 4.5v11' }],
  ['path', { d: 'M8 11.5 12 15.5l4-4' }],
  ['path', { d: 'M5 19h14' }],
])

export const ImsUploadIcon = createImsIcon('ImsUploadIcon', [
  ['path', { d: 'M12 15.5v-11' }],
  ['path', { d: 'M8 8.5 12 4.5l4 4' }],
  ['path', { d: 'M5 19h14' }],
])

export const ImsExternalLinkIcon = createImsIcon('ImsExternalLinkIcon', [
  ['path', { d: 'M14 5h5v5' }],
  ['path', { d: 'M10.5 13.5 19 5' }],
  ['path', { d: 'M18.5 13.5v5h-13v-13h5' }],
])

export const ImsClockIcon = createImsIcon('ImsClockIcon', [
  ['path', { d: 'M12 4a8 8 0 1 1 0 16 8 8 0 0 1 0-16Z' }],
  ['path', { d: 'M12 8v4l3 2' }],
])

export const ImsLoaderIcon = createImsIcon('ImsLoaderIcon', [
  ['path', { d: 'M12 4a8 8 0 1 1-8 8' }],
])

export const ImsPackagePlusIcon = createImsIcon('ImsPackagePlusIcon', [
  ['path', { d: 'M19.5 11.5V8L12 4 4.5 8v8l7.5 4 1.5-.8' }],
  ['path', { d: 'M4.5 8 12 12l7.5-4' }],
  ['path', { d: 'M12 12v8' }],
  ['path', { d: 'M17.5 14v6M14.5 17h6' }],
])

export const ImsPackageCheckIcon = createImsIcon('ImsPackageCheckIcon', [
  ['path', { d: 'M19.5 11.5V8L12 4 4.5 8v8l7.5 4 1.2-.6' }],
  ['path', { d: 'M4.5 8 12 12l7.5-4' }],
  ['path', { d: 'M12 12v8' }],
  ['path', { d: 'M14.5 17.2 16.8 19.5 21 15' }],
])

export const ImsArchiveIcon = createImsIcon('ImsArchiveIcon', [
  ['path', { d: 'M4 5h16v3.5H4V5Z' }],
  ['path', { d: 'M5.5 8.5V19h13V8.5' }],
  ['path', { d: 'M10 13h4' }],
])

export const ImsLayersIcon = createImsIcon('ImsLayersIcon', [
  ['path', { d: 'M12 4.5 20 9l-8 4.5L4 9l8-4.5Z' }],
  ['path', { d: 'M4 13.5 12 18l8-4.5' }],
])

export const ImsStoreIcon = createImsIcon('ImsStoreIcon', [
  ['path', { d: 'M4 9.5 5.5 5h13L20 9.5' }],
  ['path', { d: 'M5.5 9.5V19h13V9.5' }],
])

export const ImsSpreadsheetIcon = createImsIcon('ImsSpreadsheetIcon', [
  ['path', { d: 'M6 4h8l4 4v12H6V4Z' }],
  ['path', { d: 'M9 12.5h6M9 16h4' }],
])

export const ImsImagePlusIcon = createImsIcon('ImsImagePlusIcon', [
  ['path', { d: 'M4 5.5h14v14H4v-14Z' }],
  ['path', { d: 'M4 16.5 9 12l4 3.5 2.5-2 2.5 2' }],
  ['path', { d: 'M19.5 3.5v4M17.5 5.5h4' }],
])

export const ImsMailIcon = createImsIcon('ImsMailIcon', [
  ['path', { d: 'M4 6h16v12H4V6Z' }],
  ['path', { d: 'M4 6.5 12 12.5l8-6' }],
])

export const ImsMailCheckIcon = createImsIcon('ImsMailCheckIcon', [
  ['path', { d: 'M20 11.5V6H4v12h8' }],
  ['path', { d: 'M4 6.5 12 12.5l8-6' }],
  ['path', { d: 'M14.5 17 16.8 19.2 21 14.8' }],
])

export const ImsPhoneIcon = createImsIcon('ImsPhoneIcon', [
  ['path', { d: 'M6.5 4.5h3l1.4 3.8-2 1.6a10.5 10.5 0 0 0 5.2 5.2l1.6-2 3.8 1.4v3l-1.8 1.8A13.2 13.2 0 0 1 4.7 6.3l1.8-1.8Z' }],
])

export const ImsEyeIcon = createImsIcon('ImsEyeIcon', [
  ['path', { d: 'M3 12c2-4 5.2-6 9-6s7 2 9 6c-2 4-5.2 6-9 6s-7-2-9-6Z' }],
  ['path', { d: 'M12 10a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z' }],
])

export const ImsStarIcon = createImsIcon('ImsStarIcon', [
  ['path', { d: 'M12 4 14.4 8.9l5.4.8-3.9 3.8.9 5.4L12 16.3l-4.8 2.6.9-5.4-3.9-3.8 5.4-.8L12 4Z' }],
])

export const ImsShieldCheckIcon = createImsIcon('ImsShieldCheckIcon', [
  ['path', { d: 'M12 4 19 7v5c0 4.2-2.9 7.5-7 8.8-4.1-1.3-7-4.6-7-8.8V7l7-3Z' }],
  ['path', { d: 'M9 12.2 11.2 14.4 15.2 10' }],
])

export const ImsShieldOffIcon = createImsIcon('ImsShieldOffIcon', [
  ['path', { d: 'M12 4 19 7v5c0 1.1-.2 2.1-.6 3' }],
  ['path', { d: 'M15.4 18.8A10 10 0 0 1 12 20.8c-4.1-1.3-7-4.6-7-8.8V7l2.2-.9' }],
  ['path', { d: 'M4.5 4.5 19.5 19.5' }],
])

export const ImsKeyIcon = createImsIcon('ImsKeyIcon', [
  ['path', { d: 'M8.2 12a3.2 3.2 0 1 1 0 6.4 3.2 3.2 0 0 1 0-6.4Z' }],
  ['path', { d: 'M10.8 12.8 19 4.5' }],
  ['path', { d: 'M16.5 7 19 9.5' }],
])

export const ImsCameraIcon = createImsIcon('ImsCameraIcon', [
  ['path', { d: 'M4 8.5h3l1.5-2.5h7L17 8.5h3v10.5H4V8.5Z' }],
  ['path', { d: 'M12 11a2.8 2.8 0 1 1 0 5.6 2.8 2.8 0 0 1 0-5.6Z' }],
])

export const ImsGoogleIcon = createImsIcon('ImsGoogleIcon', [
  ['path', { d: 'M20.25 12.25c0-.65-.06-1.28-.17-1.88H12v3.56h4.62a3.95 3.95 0 0 1-1.71 2.59v2.15h2.77c1.62-1.49 2.57-3.69 2.57-6.42Z', fill: 'currentColor', stroke: 'none' }],
  ['path', { d: 'M12 20.65c2.32 0 4.27-.77 5.69-2.08l-2.77-2.15c-.77.52-1.76.83-2.92.83-2.25 0-4.15-1.52-4.83-3.56H4.31v2.22A8.64 8.64 0 0 0 12 20.65Z', fill: 'currentColor', stroke: 'none' }],
  ['path', { d: 'M7.17 13.69a5.2 5.2 0 0 1 0-3.38V8.09H4.31a8.65 8.65 0 0 0 0 7.82l2.86-2.22Z', fill: 'currentColor', stroke: 'none' }],
  ['path', { d: 'M12 6.75c1.26 0 2.4.43 3.29 1.28l2.47-2.47C16.26 4.17 14.32 3.35 12 3.35A8.64 8.64 0 0 0 4.31 8.09l2.86 2.22c.68-2.04 2.58-3.56 4.83-3.56Z', fill: 'currentColor', stroke: 'none' }],
])

export default {}
</script>