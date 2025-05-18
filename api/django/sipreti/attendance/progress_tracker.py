progress_store = {}

def set_progress(task_id, done, total):
    progress_store[task_id] = {'done': done, 'total': total}

def get_progress(task_id):
    return progress_store.get(task_id)