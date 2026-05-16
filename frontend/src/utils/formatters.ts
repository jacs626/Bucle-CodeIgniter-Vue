export function getTypeIcon(type: string): string {
  switch (type) {
    case 'company':
      return '🏢'
    case 'project':
      return '📁'
    case 'client':
      return '👤'
    case 'trip':
      return '✈️'
    case 'medication':
      return '💊'
    case 'finance':
      return '💰'
    case 'home':
      return '🏠'
    case 'restaurant':
      return '🍽️'
    case 'payment':
      return '💰'
    case 'reminder':
      return '🔔'
    case 'workflow':
      return '📋'
    case 'task':
      return '✓'
    case 'note':
      return '📝'
    default:
      return '📋'
  }
}

export function formatFileSize(bytes: number | null | undefined): string {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

export function getFileIcon(fileType: string | null | undefined): string {
  if (!fileType) return '📄'
  const type = fileType.toLowerCase()
  if (type.includes('pdf')) return '📕'
  if (type.includes('image') || type.includes('png') || type.includes('jpg')) return '🖼️'
  if (type.includes('doc') || type.includes('word')) return '📘'
  if (type.includes('sheet') || type.includes('excel')) return '📗'
  return '📄'
}

export function formatTimeAgo(date: Date): string {
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'hace un momento'
  if (diffMins < 60) return `hace ${diffMins} min`
  if (diffHours < 24) return `hace ${diffHours} horas`
  if (diffDays < 7) return `hace ${diffDays} días`
  return date.toLocaleDateString('es-ES')
}